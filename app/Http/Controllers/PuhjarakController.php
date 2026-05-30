<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Complaint;
use App\Models\News;
use App\Models\LetterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PuhjarakController extends Controller
{
    /**
     * Renders the main dashboard page.
     */
    public function index()
    {
        $newsList = News::orderBy('id', 'desc')->get();
        $aduanList = Complaint::orderBy('created_at', 'desc')->get();
        $suratList = LetterRequest::orderBy('created_at', 'desc')->get();

        return view('puhjarak', compact('newsList', 'aduanList', 'suratList'));
    }

    /**
     * Simulates authentication via NIK.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'NIK dan Kata Sandi wajib diisi!'
            ], 400);
        }

        // Attempt login via NIK
        if (Auth::attempt(['nik' => $request->nik, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Regenerate session to prevent session fixation and fetch new token
            $request->session()->regenerate();
            
            // Format initials for avatar
            $words = explode(' ', $user->name);
            $initials = '';
            foreach ($words as $w) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
            $user->imgInitials = substr($initials, 0, 2);

            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token(),
                'user' => [
                    'nama' => $user->name,
                    'nik' => $user->nik,
                    'role' => $user->role,
                    'rt' => $user->rt,
                    'rw' => $user->rw,
                    'jabatan' => $user->jabatan,
                    'imgInitials' => $user->imgInitials,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'NIK atau Kata Sandi salah!'
        ], 401);
    }

    /**
     * Logs the user session out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Stores a new complaint in the database.
     */
    public function storeAduan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string',
            'isAnonim' => 'required|string|in:0,1,true,false',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua input form aduan dengan benar.'
            ], 400);
        }

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu untuk mengirim aduan.'
            ], 401);
        }

        // Generate PJR-xxx id
        $count = Complaint::count();
        $nextId = 'PJR-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Check if ID already exists, loop until unique
        while (Complaint::where('id', $nextId)->exists()) {
            $count++;
            $nextId = 'PJR-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        // Determine submitter name
        $isAnonim = filter_var($request->isAnonim, FILTER_VALIDATE_BOOLEAN);
        if ($isAnonim) {
            $pelapor = 'Anonim';
        } else {
            $pelapor = Auth::user()->name;
        }

        // Handle Photo Upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/aduan'), $filename);
            $fotoPath = '/uploads/aduan/' . $filename;
        }

        $complaint = Complaint::create([
            'id' => $nextId,
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'pelapor' => $pelapor,
            'status' => 'pending',
            'is_anonim' => $isAnonim,
            'foto' => $fotoPath
        ]);

        return response()->json([
            'success' => true,
            'aduan' => [
                'id' => $complaint->id,
                'judul' => $complaint->judul,
                'kategori' => $complaint->kategori,
                'pelapor' => $complaint->pelapor,
                'status' => $complaint->status,
                'foto' => $complaint->foto,
                'tanggal' => 'Hari ini'
            ]
        ]);
    }

    /**
     * Updates status of a complaint (Admin only).
     */
    public function updateAduanStatus(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Akses admin diperlukan.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:diproses,selesai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.'
            ], 400);
        }

        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Aduan tidak ditemukan.'
            ], 404);
        }

        $complaint->status = $request->status;
        $complaint->save();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Stores a new letter request in the database.
     */
    public function storeSurat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|string',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua input form permohonan surat.'
            ], 400);
        }

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masuk terlebih dahulu.'
            ], 401);
        }

        $user = Auth::user();

        // Generate SRT-xxx id
        $count = LetterRequest::count();
        $nextId = 'SRT-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        while (LetterRequest::where('id', $nextId)->exists()) {
            $count++;
            $nextId = 'SRT-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        $letter = LetterRequest::create([
            'id' => $nextId,
            'jenis' => $request->jenis,
            'keterangan' => $request->keterangan,
            'pemohon' => $user->name,
            'nik' => $user->nik,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'surat' => [
                'id' => $letter->id,
                'jenis' => $letter->jenis,
                'keterangan' => $letter->keterangan,
                'pemohon' => $letter->pemohon,
                'nik' => $letter->nik,
                'status' => $letter->status,
                'tanggal' => 'Hari ini'
            ]
        ]);
    }

    /**
     * Updates status of a letter request (Admin only).
     */
    public function updateSuratStatus(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Akses admin diperlukan.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:disetujui,ditolak'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.'
            ], 400);
        }

        $letter = LetterRequest::find($id);

        if (!$letter) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan surat tidak ditemukan.'
            ], 404);
        }

        $letter->status = $request->status;
        $letter->save();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Stores a new news/agenda item (Admin only).
     */
    public function storeNews(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Akses admin diperlukan.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:berita,agenda',
            'title' => 'required|string|max:255',
            'date' => 'required|string',
            'tag' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua data berita dengan benar.'
            ], 400);
        }

        // Map tag to color class
        $colorMap = [
            'Agenda' => 'bg-amber-500 text-white',
            'Pembangunan' => 'bg-teal-600 text-white',
            'Berita' => 'bg-emerald-600 text-white',
            'Pengumuman' => 'bg-blue-600 text-white',
        ];
        $color = $colorMap[$request->tag] ?? 'bg-slate-500 text-white';

        // Handle news cover upload
        $imgPath = 'https://images.unsplash.com/photo-1590502593747-42a996133562?auto=format&fit=crop&w=600&q=80'; // default fallback
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/berita'), $filename);
            $imgPath = '/uploads/berita/' . $filename;
        }

        $news = News::create([
            'type' => $request->type,
            'title' => $request->title,
            'date' => $request->date,
            'tag' => $request->tag,
            'color' => $color,
            'img' => $imgPath,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'news' => $news
        ]);
    }

    /**
     * Updates an existing news/agenda item (Admin only).
     */
    public function updateNews(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Akses admin diperlukan.'
            ], 403);
        }

        $news = News::find($id);
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:berita,agenda',
            'title' => 'required|string|max:255',
            'date' => 'required|string',
            'tag' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Harap isi semua input dengan benar.'
            ], 400);
        }

        // Map tag to color class
        $colorMap = [
            'Agenda' => 'bg-amber-500 text-white',
            'Pembangunan' => 'bg-teal-600 text-white',
            'Berita' => 'bg-emerald-600 text-white',
            'Pengumuman' => 'bg-blue-600 text-white',
        ];
        $color = $colorMap[$request->tag] ?? 'bg-slate-500 text-white';

        $news->type = $request->type;
        $news->title = $request->title;
        $news->date = $request->date;
        $news->tag = $request->tag;
        $news->color = $color;
        $news->description = $request->description;

        // Handle news cover upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/berita'), $filename);
            $news->img = '/uploads/berita/' . $filename;
        }

        $news->save();

        return response()->json([
            'success' => true,
            'news' => $news
        ]);
    }

    /**
     * Deletes a news/agenda item (Admin only).
     */
    public function deleteNews($id)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Akses admin diperlukan.'
            ], 403);
        }

        $news = News::find($id);
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan.'
            ], 404);
        }

        $news->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
