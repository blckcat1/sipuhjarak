<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Complaint;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        User::create([
            'name' => 'Bapak Soeharto',
            'nik' => '197001012000031001',
            'email' => 'soeharto@puhjarak.desa.id',
            'password' => Hash::make('adminpassword'),
            'role' => 'admin',
            'jabatan' => 'Kepala Desa',
        ]);

        User::create([
            'name' => 'Ahmad Warga',
            'nik' => '3509123456780001',
            'email' => 'ahmad@puhjarak.desa.id',
            'password' => Hash::make('wargapassword'),
            'role' => 'warga',
            'rt' => '02',
            'rw' => '01',
        ]);

        // 2. Seed Complaints
        Complaint::create([
            'id' => 'PJR-003',
            'judul' => 'Lampu Jalan Mati di RT 03',
            'kategori' => 'Infrastruktur',
            'deskripsi' => 'Lampu jalan di jalan utama RT 03 mati sejak 3 hari yang lalu, membuat jalanan gelap saat malam hari.',
            'pelapor' => 'Anonim',
            'status' => 'diproses',
            'is_anonim' => true,
        ]);

        Complaint::create([
            'id' => 'PJR-002',
            'judul' => 'Bantuan Pupuk Belum Merata',
            'kategori' => 'Pertanian',
            'deskripsi' => 'Pembagian bantuan pupuk subsidi di Dusun Krajan belum merata, mohon ditinjau kembali datanya.',
            'pelapor' => 'Sutrisno',
            'status' => 'selesai',
            'is_anonim' => false,
        ]);

        Complaint::create([
            'id' => 'PJR-001',
            'judul' => 'Jalan Berlubang Menuju Dusun',
            'kategori' => 'Infrastruktur',
            'deskripsi' => 'Akses jalan utama yang menghubungkan antar dusun banyak yang berlubang dan berbahaya bagi pengendara motor.',
            'pelapor' => 'Anonim',
            'status' => 'pending',
            'is_anonim' => true,
        ]);

        // 3. Seed News & Agendas
        News::create([
            'type' => 'agenda',
            'title' => 'Penyuluhan Stunting & Posyandu Lansia',
            'date' => '28 Mei 2026',
            'tag' => 'Agenda',
            'color' => 'bg-amber-500 text-white',
            'img' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=600&q=80',
            'description' => "Pemerintah Desa Puhjarak kembali menggelar kegiatan posyandu terintegrasi yang berfokus pada penyuluhan pencegahan stunting pada balita dan pemeriksaan kesehatan berkala bagi warga lanjut usia (lansia).\n\nKegiatan ini dilaksanakan di Balai Pertemuan Desa Puhjarak mulai pukul 08.00 WIB hingga selesai. Tim medis Puskesmas setempat turut hadir untuk memberikan layanan pemeriksaan tekanan darah, cek kadar gula darah secara gratis, serta konseling gizi bagi ibu hamil dan menyusui.\n\nKepala Desa Puhjarak, Bapak Soeharto, menyampaikan bahwa program pencegahan stunting ini merupakan salah satu program prioritas desa demi melahirkan generasi emas yang tangguh dan sehat. Partisipasi warga dalam posyandu bulan ini sangat diharapkan.",
        ]);

        News::create([
            'type' => 'berita',
            'title' => 'Penyaluran BLT Dana Desa Tahap 2 Tepat Sasaran',
            'date' => '25 Mei 2026',
            'tag' => 'Berita',
            'color' => 'bg-emerald-600 text-white',
            'img' => 'https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&w=600&q=80',
            'description' => "Pemerintah Desa Puhjarak telah berhasil menyalurkan Bantuan Langsung Tunai (BLT) yang bersumber dari Dana Desa untuk Tahap ke-2 tahun anggaran 2026.\n\nSebanyak 80 Keluarga Penerima Manfaat (KPM) menerima bantuan sebesar Rp 300.000 per bulan yang dibagikan secara transparan di Aula Kantor Desa Puhjarak. Proses verifikasi penerima dilakukan secara ketat melalui musyawarah desa (Musdes) agar bantuan benar-benar jatuh ke tangan warga yang membutuhkan, terutama bagi lansia non-potensial dan keluarga dengan anggota sakit kronis.\n\nSekretaris Desa Puhjarak, Ibu Siti Aisyah, mendampingi langsung pembagian dana tersebut untuk memastikan ketertiban. Warga menyambut baik penyaluran ini guna membantu memenuhi kebutuhan pangan pokok sehari-hari.",
        ]);

        News::create([
            'type' => 'berita',
            'title' => 'Perbaikan Irigasi Dusun Krajan Rampung 100%',
            'date' => '20 Mei 2026',
            'tag' => 'Pembangunan',
            'color' => 'bg-teal-600 text-white',
            'img' => 'https://images.unsplash.com/photo-1625244724120-1fd1d34d00f6?auto=format&fit=crop&w=600&q=80',
            'description' => "Proyek pembangunan dan rehabilitasi saluran irigasi pertanian di Dusun Krajan, Desa Puhjarak, akhirnya selesai digarap sepenuhnya dan siap mendukung musim tanam kuartal kedua.\n\nPekerjaan yang memakan waktu kurang lebih tiga minggu ini dibiayai melalui Dana Desa sektor ketahanan pangan. Saluran irigasi sepanjang 250 meter yang sebelumnya mengalami pendangkalan dan kebocoran kini telah diperkuat dengan plesteran batu kali.\n\nDengan selesainya infrastruktur vital ini, aliran air ke lebih dari 50 hektar sawah warga kini menjadi lancar dan merata. Petani Dusun Krajan menyambut gembira hasil pembangunan ini karena meminimalisir risiko kekeringan pada tanaman padi dan sayur.",
        ]);

        // 4. Seed LetterRequests
        \App\Models\LetterRequest::create([
            'id' => 'SRT-002',
            'jenis' => 'Surat Pengantar Domisili',
            'keterangan' => 'Keperluan melamar pekerjaan ke luar kota',
            'pemohon' => 'Ahmad Warga',
            'nik' => '3509123456780001',
            'status' => 'disetujui',
        ]);

        \App\Models\LetterRequest::create([
            'id' => 'SRT-001',
            'jenis' => 'Surat Keterangan Usaha',
            'keterangan' => 'Keperluan pengajuan modal UMKM Mandiri',
            'pemohon' => 'Sutrisno',
            'nik' => '3509123456780099',
            'status' => 'pending',
        ]);
    }
}
