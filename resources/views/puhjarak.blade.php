<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/svg+xml" href="https://img.icons8.com/color/48/sprout.png" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SIPUHJARAK - Sistem Informasi & Portal Pelayanan Digital Desa Puhjarak. Membantu warga mengakses informasi publik, mengajukan aduan, dan mengelola administrasi kependudukan." />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SIPUHJARAK - Portal Informasi & Pelayanan Digital Desa Puhjarak</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@1,500;1,700&display=swap" rel="stylesheet">

  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Alpine.js CDN -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
    .font-serif {
      font-family: 'Playfair Display', serif;
    }
    /* Scrollbar */
    .custom-scrollbar::-webkit-scrollbar, ::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track, ::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb, ::-webkit-scrollbar-thumb { background: rgba(52,211,153,0.18); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover, ::-webkit-scrollbar-thumb:hover { background: rgba(52,211,153,0.35); }

    /* Gradient shimmer animation */
    @keyframes shimmer {
      0% { background-position: -200% center; }
      100% { background-position: 200% center; }
    }
    /* Multi-color animated headline gradient */
    .text-gradient {
      background: linear-gradient(135deg, #a78bfa, #f472b6, #38bdf8, #a78bfa);
      background-size: 200% auto;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: shimmer 5s linear infinite;
    }
    /* Glow card effect */
    .card-glow:hover {
      box-shadow: 0 0 0 1px rgba(139,92,246,0.2), 0 20px 40px rgba(0,0,0,0.35), 0 0 60px rgba(139,92,246,0.07);
    }
    /* Gradient border */
    .grad-border {
      position: relative;
    }
    .grad-border::before {
      content: '';
      position: absolute;
      inset: 0;
      padding: 1px;
      border-radius: inherit;
      background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(255,255,255,0.05), rgba(56,189,248,0.15));
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
    }
    /* Animated background gradient */
    @keyframes bgShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    /* Pulse dot */
    .pulse-dot {
      animation: pulseDot 2s ease-in-out infinite;
    }
    @keyframes pulseDot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.6; transform: scale(0.85); }
    }
    /* Section divider */
    .section-divider {
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(139,92,246,0.3), rgba(56,189,248,0.2), transparent);
    }
  </style>
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('puhjarak', () => ({
        activeTab: 'beranda',
        isMobileMenuOpen: false,
        activeProfilTab: 'sejarah',
        beritaFilter: 'semua',
        scrolled: false,
        toast: { show: false, message: '', type: 'success' },
        currentHeroSlide: 0,
        heroImages: [
          'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=1200&q=80',
          'https://images.unsplash.com/photo-1544650031-7ac7b4f4db73?auto=format&fit=crop&w=1200&q=80',
          'https://images.unsplash.com/photo-1590502593747-42a996133562?auto=format&fit=crop&w=1200&q=80'
        ],
        isLoggedIn: @json(Auth::check()),
        userRole: @json(Auth::check() ? Auth::user()->role : null),
        currentUser: {!! json_encode(Auth::check() ? [
          'nama' => Auth::user()->name,
          'nik' => Auth::user()->nik,
          'role' => Auth::user()->role,
          'rt' => Auth::user()->rt,
          'rw' => Auth::user()->rw,
          'jabatan' => Auth::user()->jabatan,
          'imgInitials' => collect(explode(' ', Auth::user()->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
        ] : null) !!},
        showUserDropdown: false,
        loginForm: { nik: '', password: '' },
        aduan: { judul: '', kategori: 'Infrastruktur', deskripsi: '', isAnonim: false, fotoFile: null },
        daftarAduan: @json($aduanList),
        beritaList: @json($newsList),
        daftarSurat: @json($suratList),
        formSurat: { jenis: 'Surat Pengantar Domisili', keterangan: '' },
        adminSubTab: 'aduan',
        showNewsModal: false,
        editingNewsId: null,
        selectedNews: null,
        previousTab: 'berita',
        formNews: { type: 'berita', title: '', date: '', tag: 'Berita', description: '', fotoFile: null },
        showToast(message, type = 'success') {
          this.toast = { show: true, message, type };
          setTimeout(() => { this.toast.show = false; }, 3000);
        },
        handleLogin() {
          if(!this.loginForm.nik || !this.loginForm.password) {
            this.showToast('Harap isi NIK dan Kata Sandi!', 'error');
            return;
          }
          fetch('/login', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify(this.loginForm)
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.isLoggedIn = true;
              this.userRole = res.body.user.role;
              this.currentUser = res.body.user;
              if (res.body.csrf_token) {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', res.body.csrf_token);
              }
              this.showToast('Autentikasi berhasil. Selamat datang, ' + this.currentUser.nama + '!');
              this.activeTab = 'dashboard';
              this.loginForm = { nik: '', password: '' };
            } else {
              this.showToast(res.body.message || 'Login gagal!', 'error');
            }
          })
          .catch(() => {
            this.showToast('Koneksi ke server bermasalah!', 'error');
          });
        },
        handleLogout() {
          fetch('/logout', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              this.isLoggedIn = false;
              this.userRole = null;
              this.currentUser = null;
              this.showUserDropdown = false;
              this.activeTab = 'beranda';
              if (data.csrf_token) {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
              }
              this.showToast('Anda telah berhasil keluar dari sistem.', 'info');
            }
          });
        },
        handleKirimAduan() {
          if (!this.isLoggedIn) {
            this.showToast('Silakan masuk terlebih dahulu untuk mengirim aduan!', 'error');
            return;
          }
          if(!this.aduan.judul || !this.aduan.deskripsi) {
            this.showToast('Mohon lengkapi judul dan deskripsi aduan!', 'error');
            return;
          }

          const formData = new FormData();
          formData.append('judul', this.aduan.judul);
          formData.append('kategori', this.aduan.kategori);
          formData.append('deskripsi', this.aduan.deskripsi);
          formData.append('isAnonim', this.aduan.isAnonim ? '1' : '0');
          if (this.aduan.fotoFile) {
            formData.append('foto', this.aduan.fotoFile);
          }

          fetch('/aduan', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: formData
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarAduan = [res.body.aduan, ...this.daftarAduan];
              this.aduan = { judul: '', kategori: 'Infrastruktur', deskripsi: '', isAnonim: false, fotoFile: null };
              
              // Reset file input in DOM
              const fileInput = document.getElementById('foto-aduan');
              if (fileInput) fileInput.value = '';
              
              this.showToast('Aduan berhasil dikirim!');
            } else {
              this.showToast(res.body.message || 'Gagal mengirim aduan!', 'error');
            }
          });
        },
        processAduan(id) {
          fetch('/aduan/' + id + '/status', {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'diproses' })
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarAduan = this.daftarAduan.map(a => a.id === id ? { ...a, status: 'diproses' } : a);
              this.showToast('Aduan ' + id + ' sedang diproses!');
            } else {
              this.showToast(res.body.message || 'Gagal mengubah status!', 'error');
            }
          });
        },
        completeAduan(id) {
          fetch('/aduan/' + id + '/status', {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'selesai' })
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarAduan = this.daftarAduan.map(a => a.id === id ? { ...a, status: 'selesai' } : a);
              this.showToast('Aduan ' + id + ' telah diselesaikan!');
            } else {
              this.showToast(res.body.message || 'Gagal menyelesaikan aduan!', 'error');
            }
          });
        },
        handleKirimSurat() {
          if(!this.formSurat.keterangan) {
            this.showToast('Mohon isi keterangan/keperluan pembuatan surat!', 'error');
            return;
          }
          fetch('/surat', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify(this.formSurat)
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarSurat = [res.body.surat, ...this.daftarSurat];
              this.formSurat = { jenis: 'Surat Pengantar Domisili', keterangan: '' };
              this.showToast('Permohonan surat berhasil dikirim!');
            } else {
              this.showToast(res.body.message || 'Gagal mengirim permohonan!', 'error');
            }
          });
        },
        approveSurat(id) {
          fetch('/surat/' + id + '/status', {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'disetujui' })
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarSurat = this.daftarSurat.map(s => s.id === id ? { ...s, status: 'disetujui' } : s);
              this.showToast('Permohonan surat ' + id + ' disetujui!');
            } else {
              this.showToast(res.body.message || 'Gagal menyetujui surat!', 'error');
            }
          });
        },
        rejectSurat(id) {
          fetch('/surat/' + id + '/status', {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({ status: 'ditolak' })
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              this.daftarSurat = this.daftarSurat.map(s => s.id === id ? { ...s, status: 'ditolak' } : s);
              this.showToast('Permohonan surat ' + id + ' ditolak!');
            } else {
              this.showToast(res.body.message || 'Gagal menolak surat!', 'error');
            }
          });
        },
        openAddNewsModal() {
          this.editingNewsId = null;
          this.formNews = { type: 'berita', title: '', date: '', tag: 'Berita', description: '', fotoFile: null };
          const fileInput = document.getElementById('foto-news');
          if (fileInput) fileInput.value = '';
          this.showNewsModal = true;
        },
        openEditNewsModal(item) {
          this.editingNewsId = item.id;
          this.formNews = { type: item.type, title: item.title, date: item.date, tag: item.tag, description: item.description || '', fotoFile: null };
          const fileInput = document.getElementById('foto-news');
          if (fileInput) fileInput.value = '';
          this.showNewsModal = true;
        },
        handleKirimNews() {
          if (!this.formNews.title || !this.formNews.date) {
            this.showToast('Harap isi judul dan tanggal berita!', 'error');
            return;
          }

          const formData = new FormData();
          formData.append('type', this.formNews.type);
          formData.append('title', this.formNews.title);
          formData.append('date', this.formNews.date);
          formData.append('tag', this.formNews.tag);
          formData.append('description', this.formNews.description || '');
          if (this.formNews.fotoFile) {
            formData.append('foto', this.formNews.fotoFile);
          }

          const url = this.editingNewsId ? '/berita/' + this.editingNewsId : '/berita';
          
          fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: formData
          })
          .then(res => res.json().then(data => ({ status: res.status, body: data })))
          .then(res => {
            if (res.status === 200) {
              if (this.editingNewsId) {
                this.beritaList = this.beritaList.map(n => n.id === this.editingNewsId ? res.body.news : n);
                this.showToast('Berita berhasil diperbarui!');
              } else {
                this.beritaList = [res.body.news, ...this.beritaList];
                this.showToast('Berita baru berhasil ditambahkan!');
              }
              this.showNewsModal = false;
            } else {
              this.showToast(res.body.message || 'Gagal menyimpan berita!', 'error');
            }
          });
        },
        deleteNews(id) {
          if (!confirm('Apakah Anda yakin ingin menghapus berita ini?')) return;
          
          fetch('/berita/' + id, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
              'Accept': 'application/json'
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              this.beritaList = this.beritaList.filter(n => n.id !== id);
              this.showToast('Berita berhasil dihapus!');
            } else {
              this.showToast(data.message || 'Gagal menghapus berita!', 'error');
            }
          });
        },
        init() {
          window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 20;
          });
          setInterval(() => {
            this.currentHeroSlide = (this.currentHeroSlide + 1) % this.heroImages.length;
          }, 5000);
          this.$nextTick(() => lucide.createIcons());

          this.$watch('activeTab', () => this.refreshIcons());
          this.$watch('activeProfilTab', () => this.refreshIcons());
          this.$watch('showNewsModal', () => this.refreshIcons());
          this.$watch('isLoggedIn', () => this.refreshIcons());
          this.$watch('userRole', () => this.refreshIcons());
          this.$watch('showUserDropdown', () => this.refreshIcons());
          this.$watch('adminSubTab', () => this.refreshIcons());
        },
        refreshIcons() {
          this.$nextTick(() => lucide.createIcons());
        }
      }));
    });
  </script>
</head>
<body class="min-h-screen text-slate-100 selection:bg-violet-500 selection:text-white"
      style="background: linear-gradient(135deg, #0f0c29 0%, #1a0533 20%, #0d1b3e 45%, #071e2e 70%, #0a1628 100%);"
      x-data="puhjarak">

  <!-- Ambient Background Orbs (Fixed, purely decorative) -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10" aria-hidden="true">
    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-violet-600/20 rounded-full blur-[120px]"></div>
    <div class="absolute top-1/3 -right-40 w-[500px] h-[500px] bg-indigo-500/15 rounded-full blur-[100px]"></div>
    <div class="absolute -bottom-20 left-1/3 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-[100px]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-violet-900/20 rounded-full blur-[140px]"></div>
    <div class="absolute bottom-1/3 right-1/4 w-[400px] h-[400px] bg-pink-600/10 rounded-full blur-[80px]"></div>
  </div>

  <!-- Toast Notification -->
  <div x-show="toast.show" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 translate-y-5"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 translate-y-5"
       class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border backdrop-blur-md"
       :class="{
         'bg-rose-950/90 border-rose-800/50 text-rose-100': toast.type === 'error',
         'bg-blue-950/90 border-blue-800/50 text-blue-100': toast.type === 'info',
         'bg-emerald-950/90 border-emerald-800/50 text-emerald-100': toast.type === 'success'
       }">
    <template x-if="toast.type === 'error'">
      <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
    </template>
    <template x-if="toast.type === 'info'">
      <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
    </template>
    <template x-if="toast.type === 'success'">
      <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
    </template>
    <span class="text-sm font-bold" x-text="toast.message"></span>
  </div>

  <!-- Navigation Header -->
  <nav class="fixed top-0 left-0 right-0 z-40 transition-all duration-500 border-b"
       :class="scrolled ? 'bg-slate-950/85 backdrop-blur-xl border-white/[0.06] py-3 shadow-[0_1px_0_rgba(255,255,255,0.04),0_8px_32px_rgba(0,0,0,0.4)]' : 'bg-transparent border-transparent py-5'">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
      
      <!-- Logo -->
      <button @click="activeTab = 'beranda'; window.scrollTo(0,0);" class="flex items-center gap-3 group">
        <div class="relative">
          <div class="absolute inset-0 bg-violet-500/30 blur-md rounded-xl group-hover:bg-violet-400/40 transition-all duration-300"></div>
          <div class="relative w-10 h-10 bg-gradient-to-br from-violet-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
            <i data-lucide="leaf" class="w-5 h-5"></i>
          </div>
        </div>
        <div class="text-left">
          <span class="block font-black text-white tracking-tight text-lg leading-none">SIPUHJARAK</span>
          <span class="block text-[10px] font-bold text-violet-400/60 uppercase tracking-[0.18em] mt-0.5">Portal Desa Digital</span>
        </div>
      </button>

      <!-- Desktop Navigation Links -->
      <div class="hidden md:flex items-center gap-1 bg-white/[0.04] backdrop-blur-xl border border-white/[0.08] p-1.5 rounded-full shadow-lg">
        <template x-for="tab in ['beranda', 'profil', 'berita', 'pengaduan']" :key="tab">
          <button @click="activeTab = tab; window.scrollTo(0,0);"
                  class="relative px-5 py-2.5 rounded-full transition-all duration-300 font-semibold text-sm"
                  :class="activeTab === tab 
                    ? 'bg-white/[0.12] text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_8px_rgba(0,0,0,0.25)]' 
                    : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]'">
            <span x-text="tab === 'berita' ? 'Informasi' : (tab === 'pengaduan' ? 'E-Aduan' : (tab.charAt(0).toUpperCase() + tab.slice(1)))"></span>
            <span x-show="activeTab === tab" class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-violet-400"></span>
          </button>
        </template>

        <!-- Login / Profile Module -->
        <template x-if="!isLoggedIn">
          <button @click="activeTab = 'login'; window.scrollTo(0,0);"
                  class="ml-1.5 px-5 py-2.5 rounded-full transition-all duration-300 font-bold text-sm flex items-center gap-1.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white hover:from-violet-500 hover:to-indigo-500 shadow-lg shadow-violet-950/40 hover:shadow-violet-950/60 hover:-translate-y-0.5"
                  :class="activeTab === 'login' ? 'from-violet-500 to-indigo-500' : ''">
            <i data-lucide="log-in" class="w-4 h-4"></i> Masuk
          </button>
        </template>
        
        <template x-if="isLoggedIn">
          <div class="relative ml-1.5">
            <button @click="showUserDropdown = !showUserDropdown"
                    class="flex items-center gap-2.5 pl-2 pr-4 py-1.5 bg-white/[0.06] hover:bg-white/[0.1] border border-white/10 rounded-full transition-all duration-200 shadow-sm">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-black shadow-md ring-2 ring-white/10"
                   :class="userRole === 'admin' ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600'"
                   x-text="currentUser ? currentUser.imgInitials : 'U'">
              </div>
              <div class="text-left">
                <span class="block text-sm font-bold text-white leading-none truncate max-w-[90px]" x-text="currentUser ? currentUser.nama.split(' ')[0] : 'Profil'"></span>
                <span class="block text-[10px] font-bold uppercase tracking-wider leading-none mt-0.5" :class="userRole === 'admin' ? 'text-amber-400/70' : 'text-emerald-400/70'" x-text="userRole === 'admin' ? 'Admin' : 'Warga'"></span>
              </div>
              <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-white/40 transition-transform duration-200" :class="showUserDropdown ? 'rotate-180' : ''"></i>
            </button>

            <!-- User Dropdown Menu -->
            <div x-show="showUserDropdown" 
                 @click.away="showUserDropdown = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 class="absolute right-0 mt-3 w-60 bg-slate-950/95 backdrop-blur-2xl border border-white/10 rounded-[1.75rem] shadow-[0_20px_60px_rgba(0,0,0,0.5)] overflow-hidden z-50">
              <div class="px-5 py-4 border-b border-white/[0.06]">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-black shadow-md flex-shrink-0"
                       :class="userRole === 'admin' ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600'"
                       x-text="currentUser ? currentUser.imgInitials : 'U'">
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider mb-0.5" :class="userRole === 'admin' ? 'text-amber-400/70' : 'text-emerald-400/70'" x-text="userRole === 'admin' ? 'Aparatur Desa' : 'Warga Puhjarak'"></p>
                    <p class="text-sm font-black text-white truncate" x-text="currentUser ? currentUser.nama : ''"></p>
                    <p class="text-[10px] text-white/30 font-mono mt-0.5" x-text="currentUser ? currentUser.nik : ''"></p>
                  </div>
                </div>
              </div>
              <div class="p-2">
                <button @click="activeTab = 'dashboard'; showUserDropdown = false;" 
                        class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-white/70 hover:bg-white/[0.06] hover:text-white rounded-2xl transition-colors gap-3">
                  <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-400/60"></i> Dasbor Personal
                </button>
                <button @click="showToast('Fitur pengaturan akun akan segera hadir!', 'info'); showUserDropdown = false;"
                        class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-white/70 hover:bg-white/[0.06] hover:text-white rounded-2xl transition-colors gap-3">
                  <i data-lucide="settings" class="w-4 h-4 text-white/30"></i> Pengaturan Akun
                </button>
                <div class="h-px bg-white/[0.06] my-1 mx-2"></div>
                <button @click="handleLogout()" 
                        class="w-full flex items-center px-4 py-2.5 text-sm font-bold text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 rounded-2xl transition-colors gap-3">
                  <i data-lucide="log-out" class="w-4 h-4"></i> Keluar Sesi
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Mobile Menu Button -->
      <button @click="isMobileMenuOpen = !isMobileMenuOpen" 
              class="md:hidden p-2.5 rounded-xl bg-white/[0.06] border border-white/10 text-white hover:bg-white/10 transition-colors flex items-center justify-center">
        <!-- Close icon (x) -->
        <svg x-show="isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        <!-- Menu icon (hamburger) -->
        <svg x-show="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
      </button>
    </div>
  </nav>

  <!-- Mobile Menu Panel -->
  <div x-show="isMobileMenuOpen"
       class="fixed inset-0 z-30 bg-black/50 backdrop-blur-lg md:hidden"
       @click="isMobileMenuOpen = false">
    <div class="absolute right-0 top-0 bottom-0 w-72 bg-slate-950/95 backdrop-blur-2xl p-6 shadow-2xl flex flex-col gap-6 border-l border-white/5"
         @click.stopPropagation>
      <div class="flex items-center justify-between border-b border-white/5 pb-4">
        <span class="font-black text-white text-lg">Menu Navigasi</span>
        <button @click="isMobileMenuOpen = false" class="p-2 rounded-lg bg-white/5 text-white flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>
      <div class="flex flex-col gap-3">
        <template x-for="tab in ['beranda', 'profil', 'berita', 'pengaduan']" :key="tab">
          <button @click="activeTab = tab; isMobileMenuOpen = false; window.scrollTo(0,0);"
                  class="px-5 py-3 rounded-full text-left font-bold capitalize text-sm transition-all"
                  :class="activeTab === tab ? 'bg-white/10 text-white' : 'text-white/60 hover:text-white'">
            <span x-text="tab === 'berita' ? 'Informasi' : (tab === 'pengaduan' ? 'E-Aduan' : tab)"></span>
          </button>
        </template>
        <div class="h-px bg-white/5 my-2"></div>
        
        <template x-if="!isLoggedIn">
          <button @click="activeTab = 'login'; isMobileMenuOpen = false; window.scrollTo(0,0);"
                  class="w-full px-5 py-3 rounded-full font-bold text-sm flex items-center bg-emerald-800 text-white shadow-lg">
            <i data-lucide="log-in" class="w-4 h-4 mr-2"></i> Masuk
          </button>
        </template>
        
        <template x-if="isLoggedIn">
          <div class="space-y-4">
            <div class="flex items-center gap-3 px-4 py-2 bg-white/[0.02] rounded-2xl border border-white/5">
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                   :class="userRole === 'admin' ? 'bg-amber-600' : 'bg-emerald-700'"
                   x-text="currentUser ? currentUser.imgInitials : 'U'">
              </div>
              <div>
                <p class="text-sm font-bold text-white" x-text="currentUser ? currentUser.nama : ''"></p>
                <p class="text-[10px] text-emerald-300/50 uppercase tracking-wider font-bold" x-text="userRole === 'admin' ? 'Admin' : 'Warga'"></p>
              </div>
            </div>
            <button @click="activeTab = 'dashboard'; isMobileMenuOpen = false; window.scrollTo(0,0);" 
                    class="w-full flex items-center px-5 py-2.5 text-sm font-medium text-white/80 hover:bg-white/5 rounded-2xl">
              <i data-lucide="layout-dashboard" class="w-4 h-4 mr-3"></i> Dasbor Personal
            </button>
            <button @click="handleLogout(); isMobileMenuOpen = false;" 
                    class="w-full flex items-center px-5 py-2.5 text-sm font-bold text-rose-400 hover:bg-rose-950/30 rounded-2xl">
              <i data-lucide="log-out" class="w-4 h-4 mr-3"></i> Keluar Sesi
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <main class="max-w-7xl mx-auto px-6 pt-32 pb-24 min-h-[85vh]">
    
    <!-- === BERANDA (HOME) === -->
    <div x-show="activeTab === 'beranda'"
         class="space-y-16 animate-in fade-in duration-700">

      <!-- Hero Section -->
      <div class="flex flex-col lg:flex-row gap-10 items-center min-h-[75vh] pt-8">
        <div class="lg:w-1/2 space-y-8 relative z-10">
          
          <!-- Status badge -->
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-500/[0.10] backdrop-blur-md border border-indigo-500/25 text-indigo-300 text-sm font-semibold">
            <span class="flex h-2 w-2 relative mr-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            Portal Resmi Desa Puhjarak, Kab. Kediri
          </div>

          <!-- Headline -->
          <div class="space-y-2">
            <div class="flex items-start gap-4">
              <div class="w-1 h-20 bg-gradient-to-b from-violet-400 via-pink-500 to-cyan-400 rounded-full mt-1 shrink-0"></div>
              <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-[1.08] tracking-tight">
                Napas <span class="text-gradient">Tradisi</span>,<br/>
                Langkah <span class="italic font-serif text-white">Inovasi.</span>
              </h1>
            </div>
          </div>

          <p class="text-white/60 text-lg md:text-xl max-w-md leading-relaxed font-medium pl-5">
            Membangun ekosistem desa yang transparan, mandiri, dan terkoneksi melalui satu pintu digital.
          </p>

          <!-- CTAs -->
          <div class="flex flex-wrap gap-4 pl-5">
            <button @click="activeTab = 'pengaduan'; window.scrollTo(0,0);"
                    class="group bg-gradient-to-r from-violet-600 to-pink-600 text-white px-8 py-4 rounded-full font-bold flex items-center hover:from-violet-500 hover:to-pink-500 transition-all shadow-xl shadow-violet-950/40 hover:shadow-violet-950/60 hover:-translate-y-1">
              Lapor Aspirasi 
              <span class="bg-white/20 p-1.5 rounded-full ml-3 group-hover:rotate-45 transition-transform duration-300">
                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
              </span>
            </button>
            <button @click="activeTab = 'berita'; window.scrollTo(0,0);"
                    class="px-8 py-4 rounded-full font-bold flex items-center gap-2.5 bg-white/[0.06] border border-white/10 text-white/80 hover:bg-white/[0.12] hover:text-white transition-all hover:-translate-y-0.5">
              <i data-lucide="newspaper" class="w-4 h-4"></i> Berita Desa
            </button>
          </div>

          <!-- Quick Stats -->
          <div class="flex gap-6 pl-5 pt-2">
            <div>
              <p class="text-2xl font-black text-violet-300">4.250</p>
              <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Jiwa</p>
            </div>
            <div class="w-px bg-white/10"></div>
            <div>
              <p class="text-2xl font-black text-cyan-300">12.5</p>
              <p class="text-xs font-bold text-white/30 uppercase tracking-wider">KM²</p>
            </div>
            <div class="w-px bg-white/10"></div>
            <div>
              <p class="text-2xl font-black text-rose-300">8</p>
              <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Layanan</p>
            </div>
          </div>
        </div>

        <!-- Slideshow Visual -->
        <div class="lg:w-1/2 relative w-full h-[480px] lg:h-[560px]">
          <!-- Glow rings -->
          <div class="absolute -inset-4 bg-violet-500/10 blur-3xl rounded-full"></div>
          <div class="absolute -inset-8 bg-pink-500/5 blur-3xl rounded-full"></div>
          <div class="absolute inset-0 rounded-[2.5rem] overflow-hidden shadow-[0_30px_80px_rgba(0,0,0,0.5)] border border-white/10 bg-indigo-950">
             <template x-for="(img, idx) in heroImages" :key="idx">
                <img :src="img" 
                     alt="Potret Desa Puhjarak"
                     class="absolute inset-0 w-full h-full object-cover transition-all duration-1000 ease-in-out" 
                     :class="idx === currentHeroSlide ? 'opacity-100 scale-100' : 'opacity-0 scale-105'" />
             </template>
             <div class="absolute inset-0 bg-gradient-to-tr from-indigo-950/70 via-violet-950/10 to-transparent"></div>
             
             <!-- Village label overlay -->
             <div class="absolute top-6 left-6 right-6 flex items-center justify-between z-20">
               <div class="bg-black/40 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-2.5 flex items-center gap-2.5">
                 <div class="w-6 h-6 rounded-lg bg-violet-500/30 flex items-center justify-center">
                   <i data-lucide="map-pin" class="w-3.5 h-3.5 text-violet-300"></i>
                 </div>
                 <div>
                   <p class="text-white font-bold text-xs leading-none">Desa Puhjarak</p>
                   <p class="text-white/40 text-[9px] font-semibold mt-0.5">Kab. Kediri, Jawa Timur</p>
                 </div>
               </div>
             </div>
             
             <!-- Slide Dots -->
             <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-20 bg-black/40 px-3 py-2 rounded-full backdrop-blur-md border border-white/10">
                <template x-for="(img, idx) in heroImages" :key="idx">
                  <button @click="currentHeroSlide = idx"
                          class="h-1.5 rounded-full transition-all duration-500"
                          :class="idx === currentHeroSlide ? 'w-8 bg-violet-400' : 'w-1.5 bg-white/30 hover:bg-white/60'"></button>
                </template>
             </div>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <div class="section-divider"></div>

      <!-- Quick Access Links -->
      <div>
        <div class="flex items-center gap-3 mb-6">
          <div class="w-1 h-5 bg-gradient-to-b from-violet-400 via-pink-500 to-cyan-400 rounded-full"></div>
          <h2 class="text-sm font-black text-white/40 uppercase tracking-[0.2em]">Layanan Cepat</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5">
          <div class="group bg-white hover:bg-white shadow-xl rounded-[1.75rem] p-6 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer card-glow border border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100/80 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300 shadow-sm">
              <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <h3 class="font-black text-slate-800 text-sm">Administrasi</h3>
            <p class="text-xs text-slate-400 font-medium mt-1">Surat menyurat</p>
            <div class="mt-4 flex items-center text-emerald-600 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">
              Selengkapnya <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
            </div>
          </div>
          <div class="group bg-white hover:bg-white shadow-xl rounded-[1.75rem] p-6 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer card-glow border border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100/80 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300 shadow-sm">
              <i data-lucide="droplets" class="w-5 h-5 text-blue-600"></i>
            </div>
            <h3 class="font-black text-slate-800 text-sm">Pertanian</h3>
            <p class="text-xs text-slate-400 font-medium mt-1">Info irigasi & pupuk</p>
            <div class="mt-4 flex items-center text-blue-600 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">
              Selengkapnya <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
            </div>
          </div>
          <div class="group bg-white hover:bg-white shadow-xl rounded-[1.75rem] p-6 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer card-glow border border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100/80 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300 shadow-sm">
              <i data-lucide="shield" class="w-5 h-5 text-amber-600"></i>
            </div>
            <h3 class="font-black text-slate-800 text-sm">Kamtibmas</h3>
            <p class="text-xs text-slate-400 font-medium mt-1">Laporan keamanan</p>
            <div class="mt-4 flex items-center text-amber-600 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">
              Selengkapnya <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
            </div>
          </div>
          <div @click="activeTab = isLoggedIn ? 'dashboard' : 'login'; window.scrollTo(0,0);"
               class="group bg-white hover:bg-white shadow-xl rounded-[1.75rem] p-6 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl cursor-pointer card-glow border border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100/80 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300 shadow-sm">
              <i data-lucide="user-check" class="w-5 h-5 text-violet-600"></i>
            </div>
            <h3 class="font-black text-slate-800 text-sm">Profil Warga</h3>
            <p class="text-xs text-slate-400 font-medium mt-1">Akses data personal</p>
            <div class="mt-4 flex items-center text-violet-600 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">
              Masuk Portal <i data-lucide="arrow-right" class="w-3 h-3 ml-1"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <div class="section-divider"></div>

      <!-- Kabar & Kegiatan Terkini (Homepage Section) -->
      <div class="space-y-8 pb-16">
        <div class="flex justify-between items-end">
          <div>
            <div class="flex items-center gap-3 mb-3">
              <div class="w-1 h-5 bg-gradient-to-b from-cyan-400 to-sky-500 rounded-full"></div>
              <p class="text-xs font-black text-white/40 uppercase tracking-[0.2em]">Terkini</p>
            </div>
            <h2 class="text-3xl font-black text-white tracking-tight">Kabar & Kegiatan Desa</h2>
            <p class="text-white/40 font-medium text-sm mt-1.5">Transparansi pembangunan dan agenda komunitas Puhjarak.</p>
          </div>
          <button @click="activeTab = 'berita'; window.scrollTo(0,0);"
                  class="text-sm font-bold text-sky-400 hover:text-sky-300 flex items-center gap-1.5 transition-colors whitespace-nowrap">
            Semua Berita <i data-lucide="chevron-right" class="w-4 h-4"></i>
          </button>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
          <template x-for="item in beritaList.slice(0, 3)" :key="item.id">
            <div @click="selectedNews = item; previousTab = 'beranda'; activeTab = 'detail-berita'; window.scrollTo(0,0);"
                 class="bg-white/[0.04] border border-white/[0.08] shadow-xl rounded-[1.75rem] flex flex-col group cursor-pointer overflow-hidden hover:-translate-y-2 hover:border-emerald-500/20 transition-all duration-500 card-glow">
              <div class="h-48 relative overflow-hidden">
                <img :src="item.img" :alt="item.title" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                <div class="absolute top-3.5 left-3.5 text-[10px] font-bold px-2.5 py-1 rounded-xl shadow-sm z-10" :class="item.color" x-text="item.tag"></div>
              </div>
              <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                  <p class="text-[10px] font-bold text-white/30 mb-2 flex items-center tracking-wide uppercase">
                    <i data-lucide="clock" class="w-3 h-3 mr-1"></i> <span x-text="item.date"></span>
                  </p>
                  <h3 class="font-extrabold text-white text-base leading-snug mb-2 group-hover:text-sky-300 transition-colors line-clamp-2" x-text="item.title"></h3>
                  <p class="text-xs text-white/40 font-medium line-clamp-2" x-text="item.description || 'Klik untuk membaca selengkapnya...'"></p>
                </div>
                <div class="flex items-center text-xs font-bold text-sky-400/80 group-hover:text-sky-300 transition-colors mt-4 gap-1">
                  Baca selengkapnya <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- === PROFIL === -->
    <div x-show="activeTab === 'profil'"
         class="animate-in fade-in duration-700 max-w-5xl mx-auto pt-10">
      
      <!-- Page Header -->
      <div class="text-center mb-14">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/[0.08] border border-emerald-500/20 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-5">
          <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Kab. Kediri, Jawa Timur
        </div>
        <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-4">Profil Puhjarak</h2>
        <p class="text-white/40 text-base max-w-xl mx-auto font-medium leading-relaxed">Mengenal lebih dekat sejarah, demografi, potensi, dan jajaran pelayan masyarakat kami.</p>
        <div class="section-divider mt-8 max-w-xs mx-auto"></div>
      </div>

      <!-- Profil Tab Buttons -->
      <div class="flex flex-wrap justify-center gap-2 mb-10">
        <button @click="activeProfilTab = 'sejarah'"
                class="flex items-center px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 border"
                :class="activeProfilTab === 'sejarah' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-lg shadow-emerald-950/20 scale-105' : 'bg-white/[0.03] text-white/50 border-white/[0.08] hover:bg-white/[0.06] hover:text-white/80'">
          <i data-lucide="book-open" class="w-4 h-4 mr-2"></i> Cerita Desa
        </button>
        <button @click="activeProfilTab = 'demografi'"
                class="flex items-center px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 border"
                :class="activeProfilTab === 'demografi' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-lg shadow-emerald-950/20 scale-105' : 'bg-white/[0.03] text-white/50 border-white/[0.08] hover:bg-white/[0.06] hover:text-white/80'">
          <i data-lucide="bar-chart-2" class="w-4 h-4 mr-2"></i> Demografi
        </button>
        <button @click="activeProfilTab = 'potensi'"
                class="flex items-center px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 border"
                :class="activeProfilTab === 'potensi' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-lg shadow-emerald-950/20 scale-105' : 'bg-white/[0.03] text-white/50 border-white/[0.08] hover:bg-white/[0.06] hover:text-white/80'">
          <i data-lucide="leaf" class="w-4 h-4 mr-2"></i> Potensi Alam
        </button>
        <button @click="activeProfilTab = 'aparatur'"
                class="flex items-center px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-300 border"
                :class="activeProfilTab === 'aparatur' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30 shadow-lg shadow-emerald-950/20 scale-105' : 'bg-white/[0.03] text-white/50 border-white/[0.08] hover:bg-white/[0.06] hover:text-white/80'">
          <i data-lucide="users" class="w-4 h-4 mr-2"></i> Aparatur Desa
        </button>
      </div>

      <div class="bg-white/[0.04] backdrop-blur-2xl border border-white/[0.08] shadow-2xl rounded-[2rem] p-8 md:p-12 min-h-[400px]">
        <!-- 1. SEJARAH -->
        <div x-show="activeProfilTab === 'sejarah'" class="grid md:grid-cols-2 gap-10 items-center">
          <div>
            <h3 class="text-3xl font-extrabold text-white mb-5 leading-tight">Berakar pada Alam,<br/>Tumbuh bersama Zaman.</h3>
            <div class="pl-4 border-l-2 border-emerald-500/40 mb-6">
              <p class="text-white/60 text-base leading-relaxed font-medium">
                Desa Puhjarak bukan sekadar wilayah administratif. Ini adalah rumah bagi masyarakat agraris yang menjunjung tinggi harmoni, gotong royong, dan kini melangkah mantap menuju era keterbukaan informasi publik.
              </p>
            </div>
            <div class="flex gap-3">
              <span class="inline-flex items-center bg-white/[0.06] text-emerald-300 px-4 py-2 rounded-xl text-sm font-bold border border-white/10">
                <i data-lucide="map" class="w-4 h-4 mr-2"></i> 12.5 KM²
              </span>
              <span class="inline-flex items-center bg-white/[0.06] text-amber-300 px-4 py-2 rounded-xl text-sm font-bold border border-white/10">
                <i data-lucide="users" class="w-4 h-4 mr-2"></i> 4.250 Jiwa
              </span>
            </div>
          </div>
          <div class="relative h-64 md:h-80 rounded-[2rem] overflow-hidden border border-white/10 flex items-center justify-center group shadow-lg">
            <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=800&q=80" alt="Sejarah Puhjarak" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 to-transparent"></div>
          </div>
        </div>

        <!-- 2. DEMOGRAFI -->
        <div x-show="activeProfilTab === 'demografi'">
           <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
              <div class="bg-white/[0.04] p-6 rounded-3xl border border-white/[0.08] text-center hover:border-emerald-500/20 transition-colors">
                <h4 class="text-4xl font-black text-white mb-1">4.250</h4>
                <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Total Jiwa</p>
              </div>
              <div class="bg-white/[0.04] p-6 rounded-3xl border border-white/[0.08] text-center hover:border-blue-500/20 transition-colors">
                <h4 class="text-4xl font-black text-blue-400 mb-1">2.100</h4>
                <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Laki-Laki</p>
              </div>
              <div class="bg-white/[0.04] p-6 rounded-3xl border border-white/[0.08] text-center hover:border-rose-500/20 transition-colors">
                <h4 class="text-4xl font-black text-rose-400 mb-1">2.150</h4>
                <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Perempuan</p>
              </div>
              <div class="bg-white/[0.04] p-6 rounded-3xl border border-white/[0.08] text-center hover:border-amber-500/20 transition-colors">
                <h4 class="text-4xl font-black text-amber-400 mb-1">1.230</h4>
                <p class="text-xs font-bold text-white/30 uppercase tracking-wider">Kepala Keluarga</p>
              </div>
           </div>
           
           <h3 class="text-xl font-extrabold text-white mb-6">Mata Pencaharian Utama</h3>
           <div class="space-y-5">
              <template x-for="item in [
                { label: 'Pertanian & Perkebunan', value: 55, color: 'bg-emerald-500' },
                { label: 'Wiraswasta / UMKM', value: 25, color: 'bg-amber-500' },
                { label: 'Pegawai (PNS/Swasta)', value: 15, color: 'bg-blue-500' },
                { label: 'Lainnya', value: 5, color: 'bg-slate-400' }
              ]">
                <div>
                  <div class="flex justify-between text-sm font-bold mb-2">
                    <span class="text-white" x-text="item.label"></span>
                    <span class="text-emerald-300/60" x-text="item.value + '%'"></span>
                  </div>
                  <div class="w-full bg-white/5 border border-white/10 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="h-full rounded-full relative overflow-hidden" :class="item.color" :style="'width: ' + item.value + '%'"></div>
                  </div>
                </div>
              </template>
           </div>
         </div>

        <!-- 3. POTENSI ALAM -->
        <div x-show="activeProfilTab === 'potensi'" class="grid md:grid-cols-2 gap-6">
           <div class="relative p-8 rounded-[2rem] overflow-hidden min-h-[300px] flex flex-col justify-end group">
              <img src="https://images.unsplash.com/photo-1590502593747-42a996133562?auto=format&fit=crop&w=800&q=80" alt="UMKM" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 z-0" />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent z-10"></div>
              <div class="relative z-20">
                <div class="w-10 h-10 bg-amber-400 rounded-xl flex items-center justify-center mb-4 text-amber-950 shadow-lg">
                  <i data-lucide="leaf" class="w-5 h-5"></i>
                </div>
                <h4 class="text-3xl font-black text-white mb-2">Pertanian Organik</h4>
                <p class="text-emerald-100/80 font-medium">Lahan persawahan Puhjarak menerapkan sistem organik yang menghasilkan komoditas beras super dan sayur mayur untuk wilayah regional.</p>
              </div>
           </div>
           <div class="relative p-8 rounded-[2rem] overflow-hidden min-h-[300px] flex flex-col justify-end group">
              <img src="https://images.unsplash.com/photo-1601662528567-526cd06f6582?auto=format&fit=crop&w=800&q=80" alt="Kerajinan" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 z-0" />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent z-10"></div>
              <div class="relative z-20">
                <div class="w-10 h-10 bg-teal-400 rounded-xl flex items-center justify-center mb-4 text-teal-950 shadow-lg">
                  <i data-lucide="wind" class="w-5 h-5"></i>
                </div>
                <h4 class="text-3xl font-black text-white mb-2">Kerajinan Kriya</h4>
                <p class="text-emerald-100/80 font-medium">Bambu lokal diolah menjadi karya seni dan peralatan rumah tangga bernilai jual tinggi. Menjadi motor penggerak ekonomi kreatif desa.</p>
              </div>
           </div>
        </div>

        <!-- 4. APARATUR DESA -->
        <div x-show="activeProfilTab === 'aparatur'">
          <div class="text-center mb-10">
            <h3 class="text-2xl font-extrabold text-white mb-2">Pemerintahan Desa</h3>
            <p class="text-emerald-300/60 font-medium">Jajaran pelayan masyarakat yang berdedikasi membangun Puhjarak.</p>
          </div>

          <!-- Kades -->
          <div class="flex justify-center mb-10">
            <div class="bg-white/[0.03] border border-white/10 p-6 rounded-[2rem] shadow-xl text-center w-full max-w-[280px] group hover:-translate-y-2 transition-transform duration-300">
              <div class="w-28 h-28 mx-auto bg-gradient-to-br from-emerald-500 to-teal-600 rounded-[1.5rem] flex items-center justify-center text-white text-4xl font-black mb-5 shadow-lg overflow-hidden border-4 border-white/10">
                <img src="https://i.pravatar.cc/150?img=11" alt="Kades" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity" />
              </div>
              <h4 class="font-black text-xl text-white mb-1">Bapak Soeharto</h4>
              <div class="inline-block px-4 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-xl text-xs font-bold uppercase tracking-wider">
                Kepala Desa
              </div>
            </div>
          </div>

          <!-- Staff -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative">
            <div class="hidden md:block absolute -top-8 left-[12.5%] right-[12.5%] h-0.5 bg-white/10"></div>
            
            <div class="bg-white/[0.02] border border-white/5 p-5 rounded-[2rem] text-center hover:bg-white/5 transition-colors duration-300 relative">
              <div class="hidden md:block absolute -top-8 left-1/2 w-0.5 h-8 bg-white/10 -translate-x-1/2"></div>
              <div class="w-20 h-20 mx-auto bg-white/5 rounded-2xl flex items-center justify-center text-emerald-300 text-2xl font-black mb-4 border border-white/10 shadow-sm overflow-hidden">
                <img src="https://i.pravatar.cc/150?img=25" alt="Staff" class="w-full h-full object-cover opacity-80" />
              </div>
              <h4 class="font-extrabold text-white text-sm md:text-base mb-1">Ibu Siti Aisyah</h4>
              <p class="text-emerald-400 font-bold text-xs">Sekretaris Desa</p>
            </div>
            
            <div class="bg-white/[0.02] border border-white/5 p-5 rounded-[2rem] text-center hover:bg-white/5 transition-colors duration-300 relative">
              <div class="hidden md:block absolute -top-8 left-1/2 w-0.5 h-8 bg-white/10 -translate-x-1/2"></div>
              <div class="w-20 h-20 mx-auto bg-white/5 rounded-2xl flex items-center justify-center text-emerald-300 text-2xl font-black mb-4 border border-white/10 shadow-sm overflow-hidden">
                <img src="https://i.pravatar.cc/150?img=26" alt="Staff" class="w-full h-full object-cover opacity-80" />
              </div>
              <h4 class="font-extrabold text-white text-sm md:text-base mb-1">Budi Santoso</h4>
              <p class="text-emerald-400 font-bold text-xs">Kaur Keuangan</p>
            </div>

            <div class="bg-white/[0.02] border border-white/5 p-5 rounded-[2rem] text-center hover:bg-white/5 transition-colors duration-300 relative">
              <div class="hidden md:block absolute -top-8 left-1/2 w-0.5 h-8 bg-white/10 -translate-x-1/2"></div>
              <div class="w-20 h-20 mx-auto bg-white/5 rounded-2xl flex items-center justify-center text-emerald-300 text-2xl font-black mb-4 border border-white/10 shadow-sm overflow-hidden">
                <img src="https://i.pravatar.cc/150?img=27" alt="Staff" class="w-full h-full object-cover opacity-80" />
              </div>
              <h4 class="font-extrabold text-white text-sm md:text-base mb-1">Ahmad Fauzi</h4>
              <p class="text-emerald-400 font-bold text-xs">Kasi Pemerintahan</p>
            </div>

            <div class="bg-white/[0.02] border border-white/5 p-5 rounded-[2rem] text-center hover:bg-white/5 transition-colors duration-300 relative">
              <div class="hidden md:block absolute -top-8 left-1/2 w-0.5 h-8 bg-white/10 -translate-x-1/2"></div>
              <div class="w-20 h-20 mx-auto bg-white/5 rounded-2xl flex items-center justify-center text-emerald-300 text-2xl font-black mb-4 border border-white/10 shadow-sm overflow-hidden">
                <img src="https://i.pravatar.cc/150?img=28" alt="Staff" class="w-full h-full object-cover opacity-80" />
              </div>
              <h4 class="font-extrabold text-white text-sm md:text-base mb-1">Joko Widodo</h4>
              <p class="text-emerald-400 font-bold text-xs">Kasun Krajan</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- === INFORMASI (NEWS) === -->
    <div x-show="activeTab === 'berita'"
         class="animate-in fade-in duration-700 max-w-6xl mx-auto pt-10">
      <div class="flex flex-col md:flex-row justify-between md:items-end mb-10 gap-6">
        <div>
          <div class="flex items-center gap-3 mb-3">
            <div class="w-1 h-5 bg-gradient-to-b from-rose-400 to-pink-500 rounded-full"></div>
            <p class="text-xs font-black text-white/40 uppercase tracking-[0.2em]">Portal Informasi</p>
          </div>
          <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-2">Informasi Desa</h2>
          <p class="text-white/40 text-base font-medium">Transparansi kabar pembangunan dan agenda kegiatan Puhjarak.</p>
        </div>
        <div class="flex bg-white/[0.04] p-1.5 rounded-2xl border border-white/[0.08] shadow-sm shrink-0">
          <template x-for="filter in ['semua', 'berita', 'agenda']">
             <button @click="beritaFilter = filter"
                     class="px-5 py-2 rounded-xl text-sm font-bold capitalize transition-all"
                     :class="beritaFilter === filter ? 'bg-white/[0.12] text-white shadow-md' : 'text-white/40 hover:text-white/70'"
                     x-text="filter">
             </button>
          </template>
        </div>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="item in beritaList" :key="item.id">
          <div x-show="beritaFilter === 'semua' || item.type === beritaFilter"
               @click="selectedNews = item; previousTab = 'berita'; activeTab = 'detail-berita'; window.scrollTo(0,0);"
               class="bg-white/[0.04] border border-white/[0.08] shadow-xl rounded-[1.75rem] flex flex-col group cursor-pointer overflow-hidden hover:-translate-y-2 hover:border-violet-500/20 transition-all duration-500 card-glow">
            <div class="h-52 relative overflow-hidden">
              <img :src="item.img" :alt="item.title" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
              <div class="absolute top-3.5 left-3.5 text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm z-10" :class="item.color" x-text="item.tag"></div>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <p class="text-[10px] font-bold text-white/30 mb-2.5 flex items-center tracking-wide uppercase">
                  <i data-lucide="clock" class="w-3 h-3 mr-1.5"></i> <span x-text="item.date"></span>
                </p>
                <h3 class="font-extrabold text-white text-base leading-tight mb-2 group-hover:text-violet-300 transition-colors line-clamp-2" x-text="item.title"></h3>
                <p class="text-xs text-white/40 font-medium line-clamp-2" x-text="item.description || 'Klik untuk membaca selengkapnya...'"></p>
              </div>
              <div class="flex items-center text-xs font-bold text-violet-400/80 group-hover:text-violet-300 transition-colors mt-4 gap-1">
                Baca selengkapnya <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- === DETAIL INFORMASI & KEGIATAN === -->
    <div x-show="activeTab === 'detail-berita'"
         class="animate-in fade-in duration-700 max-w-4xl mx-auto pt-10"
         style="display: none;">
      
      <!-- Back Navigation Header -->
      <div class="mb-8">
        <button @click="activeTab = previousTab; window.scrollTo(0,0);"
                class="inline-flex items-center gap-2.5 pl-3 pr-5 py-2.5 bg-white/[0.05] hover:bg-white/[0.08] backdrop-blur-md border border-white/10 rounded-full transition-all shadow-sm hover:shadow text-white font-bold text-sm">
          <i data-lucide="arrow-left" class="w-4 h-4 text-emerald-300"></i>
          <span>Kembali ke <span x-text="previousTab === 'beranda' ? 'Halaman Utama' : 'Informasi Desa'"></span></span>
        </button>
      </div>

      <!-- Detail Card -->
      <template x-if="selectedNews">
        <div class="bg-white/[0.03] border border-white/10 shadow-[0_10px_40px_rgba(0,0,0,0.3)] rounded-[2.5rem] overflow-hidden backdrop-blur-2xl">
          <!-- Cover Image Section -->
          <div class="h-96 relative overflow-hidden">
            <img :src="selectedNews.img" :alt="selectedNews.title" class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
            
            <div class="absolute bottom-6 left-6 right-6 text-white space-y-3">
              <span class="inline-block text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm animate-pulse" :class="selectedNews.color" x-text="selectedNews.tag"></span>
              <p class="text-xs font-semibold flex items-center opacity-80">
                <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 text-teal-300"></i>
                <span x-text="selectedNews.date"></span>
              </p>
            </div>
          </div>

          <!-- Content Section -->
          <div class="p-8 md:p-12 space-y-6">
            <h1 class="text-3xl md:text-5xl font-black text-white leading-tight" x-text="selectedNews.title"></h1>
            
            <div class="h-px bg-white/10 w-full my-6"></div>

            <!-- Body Text -->
            <div class="prose max-w-none text-emerald-100/80 text-base md:text-lg leading-relaxed font-medium whitespace-pre-line"
                 x-text="selectedNews.description || 'Tidak ada deskripsi detail untuk kegiatan ini.'">
            </div>

            <!-- Decorative footer signature -->
            <div class="pt-8 border-t border-white/5 mt-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold text-xs">P</div>
                <div>
                  <p class="text-xs font-bold text-white">Tim Humas Desa Puhjarak</p>
                  <p class="text-[10px] text-emerald-300/40 font-semibold uppercase tracking-wider">Pemerintah Kabupaten Kediri</p>
                </div>
              </div>
              <div class="flex gap-2">
                <button @click="showToast('Link berhasil disalin ke clipboard!', 'success')"
                        class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-xs font-bold transition-all border border-white/10 flex items-center gap-1.5">
                  <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin Tautan
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- === E-ADUAN (PENGADUAN) === -->
    <div x-show="activeTab === 'pengaduan'"
         class="animate-in fade-in duration-700 max-w-6xl mx-auto pt-10">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-500/[0.08] border border-rose-500/20 text-rose-300 text-xs font-bold uppercase tracking-wider mb-5">
          <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Layanan Aduan Desa
        </div>
        <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight mb-4">E-Aduan Warga</h2>
        <p class="text-white/40 text-base font-medium leading-relaxed">Sampaikan keluhan, aspirasi, atau masukan. Laporan Anda dipantau langsung oleh perangkat desa.</p>
        <div class="section-divider mt-6 max-w-xs mx-auto"></div>
      </div>

      <div class="grid lg:grid-cols-5 gap-8 items-start">
        <!-- Form Pengaduan -->
        <div class="lg:col-span-2 space-y-6 relative z-20">
          <div class="bg-white/[0.03] border border-white/10 shadow-2xl rounded-[2rem] p-8">
            <!-- Form Pengaduan (Hanya jika sudah login) -->
            <div x-show="isLoggedIn">
              <h3 class="text-2xl font-extrabold text-white mb-6 flex items-center">
                <i data-lucide="message-square" class="w-6 h-6 mr-3 text-emerald-400"></i> Tulis Laporan
              </h3>
              
              <form @submit.prevent="handleKirimAduan()" class="space-y-5">
                <div>
                  <label class="block text-sm font-bold text-white mb-2 ml-1">Judul Laporan</label>
                  <input type="text" 
                         x-model="aduan.judul"
                         class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner"
                         placeholder="Singkat dan jelas..." />
                </div>
                
                <div>
                  <label class="block text-sm font-bold text-white mb-2 ml-1">Kategori</label>
                  <select x-model="aduan.kategori"
                          class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner appearance-none cursor-pointer">
                    <option class="bg-slate-950 text-white">Infrastruktur</option>
                    <option class="bg-slate-950 text-white">Pelayanan Publik</option>
                    <option class="bg-slate-950 text-white">Pertanian</option>
                    <option class="bg-slate-950 text-white">Sosial & Keamanan</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-bold text-white mb-2 ml-1">Detail Kronologi</label>
                  <textarea x-model="aduan.deskripsi"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner h-32 resize-none"
                            placeholder="Ceritakan lokasi dan detail masalah..."></textarea>
                </div>

                <div>
                  <label class="block text-sm font-bold text-white mb-2 ml-1">Unggah Foto Bukti (Opsional)</label>
                  <div class="relative bg-white/5 border border-white/10 rounded-2xl px-5 py-4 flex items-center gap-3 shadow-inner hover:bg-white/10 transition-colors">
                    <i data-lucide="image" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                    <input type="file" 
                           id="foto-aduan"
                           accept="image/*"
                           @change="aduan.fotoFile = $event.target.files[0]"
                           class="w-full text-sm font-bold text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20 file:cursor-pointer cursor-pointer" />
                  </div>
                  <p class="text-[10px] text-white/30 mt-1.5 ml-1 font-semibold">Format JPG/PNG/GIF, Maks. 2MB.</p>
                </div>

                <div class="flex items-center p-4 bg-amber-500/10 rounded-2xl border border-amber-500/20">
                  <input type="checkbox" 
                         id="anonim" 
                         x-model="aduan.isAnonim"
                         class="w-5 h-5 text-amber-600 rounded-md border-amber-300 focus:ring-amber-500 accent-amber-600 cursor-pointer" />
                  <label htmlFor="anonim" class="ml-3 text-sm font-bold text-amber-200 cursor-pointer select-none">
                    Kirim sebagai Anonim (Rahasiakan Nama)
                  </label>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-violet-600 to-pink-600 text-white font-bold rounded-2xl px-5 py-4 hover:from-violet-500 hover:to-pink-500 hover:shadow-xl hover:shadow-violet-950/40 hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2 mt-2">
                  <i data-lucide="send" class="w-4 h-4"></i> Kirim Laporan
                </button>
              </form>
            </div>

            <!-- Pesan login (Jika belum login) -->
            <div x-show="!isLoggedIn" class="text-center py-6 px-2 space-y-6">
              <div class="w-16 h-16 mx-auto bg-white/5 rounded-full flex items-center justify-center text-white border border-white/10 shadow-sm">
                <i data-lucide="lock" class="w-8 h-8"></i>
              </div>
              <h4 class="text-xl font-bold text-white">Akses Terbatas</h4>
              <p class="text-sm font-medium text-emerald-300/60 leading-relaxed">
                Demi validitas laporan dan keamanan data warga, Anda harus masuk ke akun warga terlebih dahulu untuk menyampaikan laporan atau aduan.
              </p>
              <button @click="activeTab = 'login'; window.scrollTo(0,0);" 
                      class="inline-flex items-center justify-center bg-emerald-800 text-white font-bold rounded-2xl px-6 py-3.5 hover:bg-emerald-700 hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm w-full">
                Masuk Sekarang <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Radar Tracking Live -->
        <div class="lg:col-span-3">
          <div class="bg-white/[0.03] rounded-[2.5rem] p-8 min-h-[600px] relative overflow-hidden shadow-2xl border border-white/10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-teal-500/10 rounded-full blur-[80px] pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between md:items-center mb-8 gap-4 border-b border-white/5 pb-6">
              <h3 class="text-2xl font-black text-white flex items-center">
                <i data-lucide="activity" class="w-6 h-6 mr-3 text-teal-400"></i> Radar Status
              </h3>
              <span class="flex items-center text-xs font-bold text-white bg-white/5 border border-white/10 px-3 py-1.5 rounded-full">
                <span class="w-2 h-2 bg-teal-400 rounded-full mr-2 animate-ping"></span> Live Tracking
              </span>
            </div>

            <div class="relative z-10 space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
              <template x-for="item in daftarAduan" :key="item.id">
                <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5 hover:bg-white/[0.05] transition-colors flex flex-col sm:flex-row sm:items-center gap-4">
                  <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                      <span class="text-xs font-mono font-bold tracking-wider text-emerald-400/80" x-text="item.id"></span>
                      <span class="text-[10px] font-bold text-teal-300 bg-teal-500/10 px-2 py-0.5 rounded border border-teal-500/20" x-text="item.kategori"></span>
                    </div>
                    <h4 class="text-white font-bold text-lg leading-snug mb-1" x-text="item.judul"></h4>
                    <p class="text-xs text-white/70 mb-2 font-medium" x-text="item.deskripsi || ''"></p>
                    <template x-if="item.foto">
                      <div class="mb-3">
                        <a :href="item.foto" target="_blank" class="inline-block group/img">
                          <img :src="item.foto" alt="Bukti Foto" class="w-24 h-24 object-cover rounded-xl border border-white/10 hover:scale-105 transition-transform duration-300" />
                          <span class="block text-[10px] text-teal-400/60 mt-1 font-semibold group-hover/img:text-teal-300">Lihat Foto Bukti &rarr;</span>
                        </a>
                      </div>
                    </template>
                    <p class="text-xs font-semibold text-white/40 flex items-center">
                      <i data-lucide="user" class="w-3.5 h-3.5 mr-1.5 opacity-70"></i> <span x-text="item.pelapor"></span> &nbsp;&bull;&nbsp; <span x-text="item.tanggal || 'Hari Ini'"></span>
                    </p>
                  </div>
                  
                  <div class="flex-shrink-0 mt-2 sm:mt-0">
                    <span x-show="item.status === 'pending'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-white/5 text-white border border-white/10 text-xs font-bold">
                      <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 opacity-70"></i> Menunggu
                    </span>
                    <span x-show="item.status === 'diproses'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                      <i data-lucide="activity" class="w-3.5 h-3.5 mr-1.5 opacity-70"></i> Diproses
                    </span>
                    <span x-show="item.status === 'selesai'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-bold">
                      <i data-lucide="check-circle-2" class="w-3.5 h-3.5 mr-1.5 opacity-70"></i> Selesai
                    </span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- === LAYAR LOGIN === -->
    <div x-show="activeTab === 'login'"
         class="min-h-[75vh] flex items-center justify-center pt-10 animate-in fade-in duration-700">
      <div class="w-full max-w-4xl grid md:grid-cols-2 gap-8 items-center bg-white/[0.03] border border-white/10 rounded-[3rem] p-4 shadow-2xl overflow-hidden relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-teal-500/10 to-transparent rounded-full blur-3xl -z-10 pointer-events-none"></div>

        <!-- Kolom Kiri: Branding -->
        <div class="hidden md:flex flex-col justify-between h-full p-10 bg-slate-950/60 border border-white/5 rounded-[2.5rem] relative overflow-hidden group min-h-[500px]">
          <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=800&q=80" alt="Pemandangan Desa" class="absolute inset-0 w-full h-full object-cover opacity-10 mix-blend-luminosity group-hover:scale-105 transition-transform duration-1000" />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"></div>
          
          <div class="relative z-10">
            <div class="w-14 h-14 bg-white/5 rounded-2xl flex items-center justify-center text-emerald-300 border border-white/10 mb-6">
              <i data-lucide="leaf" class="w-7 h-7"></i>
            </div>
            <h2 class="text-4xl font-black text-white leading-tight mb-4">SIPUHJARAK<br/>ID Warga</h2>
            <p class="text-emerald-200/80 text-lg font-medium leading-relaxed">
              Satu gerbang akses untuk seluruh layanan digital, administrasi kependudukan, dan pelaporan masalah desa.
            </p>
          </div>

          <div class="relative z-10 bg-white/5 border border-white/10 p-5 rounded-2xl">
            <div class="flex items-center gap-3">
              <i data-lucide="shield" class="w-6 h-6 text-emerald-400"></i>
              <p class="text-sm font-semibold text-emerald-100/80">Data NIK Anda dilindungi dengan enkripsi standar industri.</p>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Form -->
        <div class="p-8 md:p-12 relative z-10">
          <div class="mb-10 text-center md:text-left">
            <h3 class="text-3xl font-extrabold text-white mb-2">Selamat Datang</h3>
            <p class="text-emerald-300/60 font-medium">Silakan masukkan NIK dan Kata Sandi Anda.</p>
          </div>

          <form class="space-y-6" @submit.prevent="handleLogin()">
            <div>
              <label class="block text-sm font-bold text-white mb-2 ml-1">Nomor Induk Kependudukan (NIK)</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                  <i data-lucide="user" class="w-5 h-5 text-white/30"></i>
                </div>
                <input type="text" 
                       x-model="loginForm.nik"
                       class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner"
                       placeholder="Masukkan NIK Anda" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-bold text-white mb-2 ml-1">Kata Sandi</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                  <i data-lucide="key" class="w-5 h-5 text-white/30"></i>
                </div>
                <input type="password" 
                       x-model="loginForm.password"
                       class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner"
                       placeholder="••••••••" />
              </div>
              <div class="flex justify-end mt-2">
                <a href="#" class="text-sm font-bold text-emerald-400 hover:text-emerald-300 transition-colors">Lupa Kata Sandi?</a>
              </div>
            </div>

            <div class="pt-4 space-y-3">
              <button type="submit" 
                      class="w-full bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold rounded-2xl px-5 py-4 hover:from-violet-500 hover:to-indigo-500 hover:shadow-xl hover:shadow-violet-950/40 hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2">
                <i data-lucide="log-in" class="w-5 h-5"></i> Masuk ke Portal
              </button>
            </div>
          </form>

          <div class="mt-8 pt-6 border-t border-white/5 text-center">
            <p class="text-xs font-bold text-white/30 uppercase tracking-wider mb-3">Klik untuk Isi Otomatis (Demo)</p>
            <div class="flex justify-center gap-3">
              <button @click="loginForm.nik = '3509123456780001'; loginForm.password = 'wargapassword';"
                      type="button"
                      class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white border border-white/10 rounded-xl text-xs font-bold transition-colors">
                Akun Warga
              </button>
              <button @click="loginForm.nik = '197001012000031001'; loginForm.password = 'adminpassword';"
                      type="button"
                      class="px-4 py-2 bg-white/5 hover:bg-white/10 text-amber-300 border border-white/10 rounded-xl text-xs font-bold transition-colors">
                Akun Admin
              </button>
            </div>
            <p class="text-[10px] text-white/30 mt-3 font-medium">
              Warga: NIK 3509123456780001 / Sandi: wargapassword<br>
              Admin: NIK 197001012000031001 / Sandi: adminpassword
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- === DASBOR PERSONAL === -->
    <div x-show="activeTab === 'dashboard'">
      <template x-if="isLoggedIn">
        <div class="animate-in fade-in duration-700 pt-10">
          
          <!-- ADMIN DASHBOARD -->
          <div x-show="userRole === 'admin'">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
              <div>
                <div class="inline-flex items-center px-3 py-1 bg-amber-500/10 text-amber-300 rounded-full text-xs font-bold uppercase tracking-wider mb-3 border border-amber-500/20">
                  <i data-lucide="shield" class="w-3 h-3 mr-1.5"></i> Akses Administrator
                </div>
                <h2 class="text-4xl font-black text-white tracking-tight">Dasbor Manajemen Desa</h2>
                <p class="text-white/40 font-medium text-base mt-1">Selamat bertugas, <span x-text="currentUser ? currentUser.nama : ''"></span>.</p>
              </div>
              <button @click="openAddNewsModal()" 
                      class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-5 py-2.5 rounded-xl font-bold flex items-center hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-950/30 transition-all hover:-translate-y-0.5 shrink-0">
                <i data-lucide="book-open" class="w-4 h-4 mr-2"></i> Tulis Berita Baru
              </button>
            </div>
 
            <!-- Statistik Admin -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
              <div class="bg-white/[0.03] border border-white/10 shadow-xl rounded-[2rem] p-6">
                <div class="flex justify-between items-start mb-4">
                  <div class="w-12 h-12 bg-rose-500/15 rounded-2xl flex items-center justify-center text-rose-400"><i data-lucide="message-square" class="w-6 h-6"></i></div>
                  <span class="text-xs font-bold text-rose-400 bg-rose-500/10 px-2 py-1 rounded-md border border-rose-500/20">Butuh Respon</span>
                </div>
                <h3 class="text-3xl font-black text-white mb-1" x-text="daftarAduan.filter(a => a.status === 'pending').length"></h3>
                <p class="text-sm font-semibold text-emerald-300/50">Aduan Warga Pending</p>
              </div>
              
              <div class="bg-white/[0.03] border border-white/10 shadow-xl rounded-[2rem] p-6">
                <div class="flex justify-between items-start mb-4">
                  <div class="w-12 h-12 bg-emerald-500/15 rounded-2xl flex items-center justify-center text-emerald-400"><i data-lucide="check-circle-2" class="w-6 h-6"></i></div>
                  <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-md border border-emerald-500/20">Total Selesai</span>
                </div>
                <h3 class="text-3xl font-black text-white mb-1" x-text="daftarAduan.filter(a => a.status === 'selesai').length"></h3>
                <p class="text-sm font-semibold text-emerald-300/50">Aduan Diselesaikan</p>
              </div>
              
              <div class="bg-white/[0.03] border border-white/10 shadow-xl rounded-[2rem] p-6">
                <div class="flex justify-between items-start mb-4">
                  <div class="w-12 h-12 bg-blue-500/15 rounded-2xl flex items-center justify-center text-blue-400"><i data-lucide="file-text" class="w-6 h-6"></i></div>
                  <span x-show="daftarSurat.filter(s => s.status === 'pending').length > 0" class="text-xs font-bold text-blue-400 bg-blue-500/10 px-2 py-1 rounded-md border border-blue-500/20 animate-pulse">Menunggu</span>
                </div>
                <h3 class="text-3xl font-black text-white mb-1" x-text="daftarSurat.filter(s => s.status === 'pending').length"></h3>
                <p class="text-sm font-semibold text-emerald-300/50">Permohonan Surat Masuk</p>
              </div>
            </div>
 
            <!-- Sub Tab Navigation -->
            <div class="flex border-b border-white/10 mb-6">
              <button @click="adminSubTab = 'aduan'"
                      class="px-6 py-3 font-bold text-sm border-b-2 transition-all"
                      :class="adminSubTab === 'aduan' ? 'border-emerald-400 text-white' : 'border-transparent text-white/40 hover:text-white'">
                Aduan & Aspirasi (<span x-text="daftarAduan.length"></span>)
              </button>
              <button @click="adminSubTab = 'surat'"
                      class="px-6 py-3 font-bold text-sm border-b-2 transition-all"
                      :class="adminSubTab === 'surat' ? 'border-emerald-400 text-white' : 'border-transparent text-white/40 hover:text-white'">
                Administrasi Layanan Surat (<span x-text="daftarSurat.length"></span>)
              </button>
              <button @click="adminSubTab = 'berita'"
                      class="px-6 py-3 font-bold text-sm border-b-2 transition-all"
                      :class="adminSubTab === 'berita' ? 'border-emerald-400 text-white' : 'border-transparent text-white/40 hover:text-white'">
                Manajemen Berita & Agenda (<span x-text="beritaList.length"></span>)
              </button>
            </div>
 
            <!-- Admin Content Card -->
            <div class="bg-white/[0.03] border border-white/10 shadow-2xl rounded-[2rem] p-8">
               
               <!-- TAB ADUAN -->
               <div x-show="adminSubTab === 'aduan'">
                 <h3 class="text-xl font-extrabold text-white mb-6">Daftar Laporan Warga</h3>
                 <div class="overflow-x-auto">
                   <table class="w-full text-left border-collapse">
                     <thead>
                       <tr class="border-b border-white/10">
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">ID</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Topik & Kategori</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Pelapor</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Status</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider text-right">Aksi</th>
                       </tr>
                     </thead>
                     <tbody class="divide-y divide-white/5">
                       <template x-for="item in daftarAduan" :key="item.id">
                         <tr class="hover:bg-white/5 transition-colors">
                           <td class="py-4 font-mono text-sm font-semibold text-white" x-text="item.id"></td>
                           <td class="py-4">
                               <p class="font-bold text-white text-sm mb-0.5" x-text="item.judul"></p>
                               <p class="text-xs font-semibold text-emerald-300/60 mb-1" x-text="item.kategori"></p>
                               <p class="text-xs text-white/60 bg-white/[0.03] p-2 rounded-xl border border-white/10 mt-1.5 font-medium max-w-lg" x-text="item.deskripsi"></p>
                               <template x-if="item.foto">
                                 <div class="mt-2">
                                   <a :href="item.foto" target="_blank" class="inline-block group/img">
                                     <img :src="item.foto" alt="Bukti Foto" class="w-16 h-16 object-cover rounded-lg border border-white/10 hover:scale-105 transition-transform" />
                                   </a>
                                 </div>
                               </template>
                            </td>
                            <td class="py-4 text-sm font-semibold text-white/70" x-text="item.pelapor"></td>
                            <td class="py-4">
                               <span x-show="item.status === 'pending'" class="px-3 py-1 bg-rose-500/10 text-rose-300 border border-rose-500/20 rounded-full text-xs font-bold">Pending</span>
                               <span x-show="item.status === 'diproses'" class="px-3 py-1 bg-amber-500/10 text-amber-300 border border-amber-500/20 rounded-full text-xs font-bold">Diproses</span>
                               <span x-show="item.status === 'selesai'" class="px-3 py-1 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded-full text-xs font-bold">Selesai</span>
                            </td>
                            <td class="py-4 text-right">
                               <div class="flex justify-end gap-2">
                                 <button x-show="item.status === 'pending'"
                                         @click="processAduan(item.id)"
                                         class="px-3 py-1.5 bg-amber-600 text-white rounded-lg text-xs font-bold hover:bg-amber-500 transition-colors shadow-lg shadow-amber-950/20">
                                   Proses
                                 </button>
                                 <button x-show="item.status === 'diproses'"
                                         @click="completeAduan(item.id)"
                                         class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-xs font-bold hover:bg-teal-500 transition-colors shadow-lg shadow-teal-950/20">
                                   Selesaikan
                                 </button>
                                 <span x-show="item.status === 'selesai'" class="text-xs text-teal-400 font-bold flex items-center justify-end"><i data-lucide="check-circle-2" class="w-4 h-4 mr-1"></i> Selesai</span>
                               </div>
                            </td>
                         </tr>
                       </template>
                     </tbody>
                   </table>
                 </div>
               </div>

                     <!-- TAB LAYANAN SURAT -->
               <div x-show="adminSubTab === 'surat'">
                 <h3 class="text-xl font-extrabold text-white mb-6">Daftar Antrean Permohonan Dokumen</h3>
                 <div class="overflow-x-auto">
                   <table class="w-full text-left border-collapse">
                     <thead>
                       <tr class="border-b border-white/10">
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">ID</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Jenis Surat & Keperluan</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Pemohon</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Status</th>
                         <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider text-right">Aksi</th>
                       </tr>
                     </thead>
                     <tbody class="divide-y divide-white/5">
                       <template x-for="item in daftarSurat" :key="item.id">
                         <tr class="hover:bg-white/5 transition-colors">
                           <td class="py-4 font-mono text-sm font-semibold text-white" x-text="item.id"></td>
                           <td class="py-4">
                             <p class="font-bold text-white text-sm mb-0.5" x-text="item.jenis"></p>
                             <p class="text-xs font-semibold text-emerald-300/60" x-text="item.keterangan"></p>
                           </td>
                           <td class="py-4">
                             <p class="text-sm font-bold text-white" x-text="item.pemohon"></p>
                             <p class="text-xs font-mono text-emerald-300/40 mt-0.5" x-text="item.nik"></p>
                           </td>
                           <td class="py-4">
                             <span x-show="item.status === 'pending'" class="px-3 py-1 bg-blue-500/10 text-blue-300 border border-blue-500/20 rounded-full text-xs font-bold">Pending</span>
                             <span x-show="item.status === 'disetujui'" class="px-3 py-1 bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 rounded-full text-xs font-bold">Disetujui</span>
                             <span x-show="item.status === 'ditolak'" class="px-3 py-1 bg-rose-500/10 text-rose-300 border border-rose-500/20 rounded-full text-xs font-bold">Ditolak</span>
                           </td>
                           <td class="py-4 text-right">
                             <div class="flex justify-end gap-2">
                               <template x-if="item.status === 'pending'">
                                 <div class="flex gap-1.5 justify-end">
                                   <button @click="approveSurat(item.id)"
                                           class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs p-2 rounded-xl transition-colors flex items-center shadow-lg shadow-emerald-950/20"
                                           title="Setujui permohonan">
                                     <i data-lucide="check" class="w-4 h-4"></i>
                                   </button>
                                   <button @click="rejectSurat(item.id)"
                                           class="bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs p-2 rounded-xl transition-colors flex items-center shadow-lg shadow-rose-950/20"
                                           title="Tolak permohonan">
                                     <i data-lucide="ban" class="w-4 h-4"></i>
                                   </button>
                                 </div>
                               </template>
                               <span x-show="item.status === 'disetujui'" class="text-xs text-emerald-400 font-bold flex items-center justify-end"><i data-lucide="check" class="w-4 h-4 mr-1"></i> Disetujui</span>
                               <span x-show="item.status === 'ditolak'" class="text-xs text-rose-400 font-bold flex items-center justify-end"><i data-lucide="ban" class="w-4 h-4 mr-1"></i> Ditolak</span>
                             </div>
                           </td>
                         </tr>
                       </template>
                     </tbody>
                   </table>
                 </div>
               </div>                   <!-- TAB MANAJEMEN BERITA -->
                 <div x-show="adminSubTab === 'berita'">
                   <h3 class="text-xl font-extrabold text-white mb-6 flex justify-between items-center">
                     <span>Kelola Berita & Agenda Desa</span>
                     <button @click="openAddNewsModal()" 
                             class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all flex items-center shadow-lg shadow-emerald-950/20">
                       <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Berita Baru
                     </button>
                   </h3>
                   <div class="overflow-x-auto">
                     <table class="w-full text-left border-collapse">
                       <thead>
                         <tr class="border-b border-white/10">
                           <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Info Berita</th>
                           <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Tipe & Kategori</th>
                           <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider">Tanggal Rilis</th>
                           <th class="pb-3 text-xs font-bold text-white/40 uppercase tracking-wider text-right">Aksi</th>
                         </tr>
                       </thead>
                       <tbody class="divide-y divide-white/5">
                         <template x-for="item in beritaList" :key="item.id">
                           <tr class="hover:bg-white/5 transition-colors">
                             <td class="py-4">
                               <div class="flex items-center gap-3 animate-in fade-in duration-300">
                                 <template x-if="item.img">
                                   <img :src="item.img" alt="Cover" class="w-12 h-12 object-cover rounded-lg border border-white/10" />
                                 </template>
                                 <div>
                                   <p class="font-bold text-white text-sm leading-snug" x-text="item.title"></p>
                                 </div>
                               </div>
                             </td>
                             <td class="py-4 text-sm font-semibold">
                               <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full text-white" :class="item.color" x-text="item.tag"></span>
                             </td>
                             <td class="py-4 text-sm font-semibold text-white/60" x-text="item.date"></td>
                             <td class="py-4 text-right">
                               <div class="flex justify-end gap-1.5">
                                 <button @click="openEditNewsModal(item)"
                                         class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs p-2 rounded-xl transition-colors flex items-center shadow-lg shadow-blue-950/20"
                                         title="Ubah Berita">
                                   <i data-lucide="edit-3" class="w-4 h-4"></i>
                                 </button>
                                 <button @click="deleteNews(item.id)"
                                         class="bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs p-2 rounded-xl transition-colors flex items-center shadow-lg shadow-rose-950/20"
                                         title="Hapus Berita">
                                   <i data-lucide="trash-2" class="w-4 h-4"></i>
                                 </button>
                               </div>
                             </td>
                           </tr>
                         </template>
                       </tbody>
                     </table>
                   </div>
                 </div>           
             </div>
          </div>

          <!-- WARGA DASHBOARD -->
          <div x-show="userRole === 'warga'">
            <!-- Header Warga -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-900/40 via-teal-900/20 to-slate-900/40 border border-emerald-500/20 rounded-[2rem] p-8 mb-8 shadow-2xl">
              <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-10 -right-10 w-52 h-52 bg-emerald-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-teal-500/10 rounded-full blur-3xl"></div>
              </div>
              <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                  <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center shadow-lg shrink-0">
                    <span class="text-2xl font-black text-emerald-300" x-text="currentUser ? currentUser.imgInitials : 'W'"></span>
                  </div>
                  <div>
                    <div class="inline-flex items-center px-2.5 py-1 bg-emerald-500/10 text-emerald-300 rounded-full text-[10px] font-bold uppercase tracking-wider mb-2 border border-emerald-500/20">
                      <i data-lucide="user-check" class="w-3 h-3 mr-1"></i> Portal Warga
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight" x-text="'Selamat datang, ' + (currentUser ? currentUser.nama.split(' ')[0] : '') + '!'"></h2>
                    <p class="text-emerald-300/50 text-sm font-medium mt-0.5" x-text="currentUser ? 'NIK: ' + currentUser.nik : ''"></p>
                  </div>
                </div>
                <button @click="activeTab = 'pengaduan'; window.scrollTo(0,0);" 
                        class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold flex items-center hover:bg-emerald-500 shadow-lg shadow-emerald-950/30 transition-all hover:-translate-y-0.5 shrink-0">
                  <i data-lucide="message-square" class="w-4 h-4 mr-2"></i> Tulis Aduan
                </button>
              </div>
            </div>

            <!-- Stats Warga -->
            <div class="grid md:grid-cols-3 gap-5 mb-8">
              <div class="bg-white/[0.04] border border-white/10 shadow-xl rounded-[1.75rem] p-6 flex items-center gap-4 hover:bg-white/[0.06] transition-colors">
                <div class="w-14 h-14 bg-emerald-500/15 rounded-2xl flex items-center justify-center text-emerald-400 shrink-0">
                  <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="text-xs font-bold text-emerald-300/40 uppercase tracking-wider mb-1">Wilayah</p>
                  <h3 class="text-xl font-black text-white" x-text="currentUser ? 'RT ' + currentUser.rt + ' / RW ' + currentUser.rw : '-'"></h3>
                </div>
              </div>
              <div class="bg-white/[0.04] border border-white/10 shadow-xl rounded-[1.75rem] p-6 flex items-center gap-4 hover:bg-white/[0.06] transition-colors">
                <div class="w-14 h-14 bg-blue-500/15 rounded-2xl flex items-center justify-center text-blue-400 shrink-0">
                  <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="text-xs font-bold text-blue-300/40 uppercase tracking-wider mb-1">Total Aduan</p>
                  <h3 class="text-3xl font-black text-white" x-text="currentUser ? daftarAduan.filter(a => a.pelapor === currentUser.nama).length : 0"></h3>
                </div>
              </div>
              <div class="bg-white/[0.04] border border-white/10 shadow-xl rounded-[1.75rem] p-6 flex items-center gap-4 hover:bg-white/[0.06] transition-colors">
                <div class="w-14 h-14 bg-amber-500/15 rounded-2xl flex items-center justify-center text-amber-400 shrink-0">
                  <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="text-xs font-bold text-amber-300/40 uppercase tracking-wider mb-1">Surat Disetujui</p>
                  <h3 class="text-3xl font-black text-white" x-text="currentUser ? daftarSurat.filter(s => s.pemohon === currentUser.nama && s.status === 'disetujui').length : 0"></h3>
                </div>
              </div>
            </div>

            <div class="grid lg:grid-cols-5 gap-8 items-start">
              
              <!-- Kolom Kiri: Form Permohonan Surat -->
              <div class="lg:col-span-2 space-y-6">
                <div class="bg-white/[0.03] border border-white/10 shadow-2xl rounded-[2rem] p-8">
                  <h3 class="text-2xl font-extrabold text-white mb-6 flex items-center">
                    <i data-lucide="file-text" class="w-6 h-6 mr-3 text-emerald-400"></i> Permohonan Dokumen
                  </h3>
                  
                  <form @submit.prevent="handleKirimSurat()" class="space-y-5">
                    <div>
                      <label class="block text-sm font-bold text-white mb-2 ml-1">Jenis Surat</label>
                      <div class="relative">
                        <select x-model="formSurat.jenis"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-bold focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner appearance-none cursor-pointer">
                          <option class="bg-slate-950 text-white">Surat Pengantar Domisili</option>
                          <option class="bg-slate-950 text-white">Surat Keterangan Usaha</option>
                          <option class="bg-slate-950 text-white">Surat Keterangan Tidak Mampu</option>
                          <option class="bg-slate-950 text-white">Surat Keterangan Kelakuan Baik</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-white/50">
                          <i data-lucide="chevron-down" class="w-5 h-5"></i>
                        </div>
                      </div>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-bold text-white mb-2 ml-1">Keperluan / Keterangan</label>
                      <textarea x-model="formSurat.keterangan"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner h-32 resize-none"
                                placeholder="Contoh: keperluan mendaftar kuliah anak, pengajuan modal kriya..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold rounded-2xl px-5 py-4 hover:bg-emerald-500 hover:shadow-xl hover:-translate-y-1 transition-all flex justify-center items-center mt-2">
                      Kirim Permohonan <i data-lucide="plus-circle" class="w-4 h-4 ml-2"></i>
                    </button>
                  </form>
                </div>
              </div>

              <!-- Kolom Ranan: Riwayat/Status Layanan -->
              <div class="lg:col-span-3 space-y-6">
                
                <!-- Riwayat Surat -->
                <div class="bg-white/[0.03] border border-white/10 shadow-2xl rounded-[2rem] p-8">
                  <h3 class="text-xl font-extrabold text-white mb-6 flex items-center">
                    <i data-lucide="clock" class="w-5 h-5 mr-2 text-teal-400"></i> Riwayat Permohonan Dokumen Anda
                  </h3>
                  
                  <div class="space-y-4">
                    <div class="text-center py-8 text-white/30" x-show="currentUser && daftarSurat.filter(s => s.pemohon === currentUser.nama).length === 0">
                      <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
                      <p class="font-semibold text-sm">Belum ada permohonan surat.</p>
                    </div>
                    
                    <template x-if="currentUser">
                      <template x-for="surat in daftarSurat.filter(s => s.pemohon === currentUser.nama)" :key="surat.id">
                        <div class="bg-white/[0.02] border border-white/5 p-5 rounded-2xl flex justify-between items-center gap-4 hover:bg-white/[0.04] transition-colors">
                          <div>
                            <div class="flex items-center gap-2 mb-1">
                              <span class="text-xs font-mono font-bold text-emerald-400" x-text="surat.id"></span>
                              <span class="text-xs font-semibold text-white/40">&bull; <span x-text="surat.tanggal || 'Hari ini'"></span></span>
                            </div>
                            <h4 class="font-bold text-white" x-text="surat.jenis"></h4>
                            <p class="text-xs font-semibold text-emerald-300/60 mt-1" x-text="surat.keterangan"></p>
                          </div>
                          <div>
                            <span x-show="surat.status === 'pending'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-500/10 text-blue-300 border border-blue-500/20 text-xs font-bold">Diproses</span>
                            <span x-show="surat.status === 'disetujui'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 text-xs font-bold">Disetujui</span>
                            <span x-show="surat.status === 'ditolak'" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-500/10 text-rose-300 border border-rose-500/20 text-xs font-bold">Ditolak</span>
                          </div>
                        </div>
                      </template>
                    </template>
                  </div>
                </div>

                <!-- Riwayat Aduan -->
                <div class="bg-white/[0.03] border border-white/10 shadow-2xl rounded-[2rem] p-8">
                  <h3 class="text-xl font-extrabold text-white mb-6 flex items-center">
                    <i data-lucide="message-square" class="w-5 h-5 mr-2 text-rose-400"></i> Riwayat Aduan Anda
                  </h3>
                  
                  <div class="space-y-4">
                    <div class="text-center py-8 text-white/30" x-show="currentUser && daftarAduan.filter(a => a.pelapor === currentUser.nama).length === 0">
                      <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
                      <p class="font-semibold text-sm">Belum ada laporan pengaduan.</p>
                    </div>
                    
                    <template x-if="currentUser">
                      <template x-for="item in daftarAduan.filter(a => a.pelapor === currentUser.nama)" :key="item.id">
                        <div class="bg-white/[0.02] border border-white/5 p-5 rounded-2xl flex justify-between items-center gap-4 hover:bg-white/[0.04] transition-colors">
                          <div>
                            <div class="flex items-center gap-2 mb-1">
                              <span class="text-xs font-mono font-bold text-emerald-400" x-text="item.id"></span>
                              <span class="text-xs font-semibold text-white/40">&bull; <span x-text="item.tanggal || 'Hari ini'"></span></span>
                            </div>
                            <h4 class="font-bold text-white" x-text="item.judul"></h4>
                            <p class="text-xs font-semibold text-emerald-300/60 mt-1" x-text="item.deskripsi"></p>
                            <template x-if="item.foto">
                              <div class="mt-2">
                                <a :href="item.foto" target="_blank" class="inline-block group/img">
                                  <img :src="item.foto" alt="Bukti Foto" class="w-16 h-16 object-cover rounded-lg border border-white/10 hover:scale-105 transition-transform" />
                                </a>
                              </div>
                            </template>
                          </div>
                          <div>
                            <span x-show="item.status === 'pending'" class="px-3 py-1 bg-rose-500/10 text-rose-300 border border-rose-500/20 rounded-full text-xs font-bold">Pending</span>
                            <span x-show="item.status === 'diproses'" class="px-3 py-1 bg-amber-500/10 text-amber-300 border border-amber-500/20 rounded-full text-xs font-bold">Diproses</span>
                            <span x-show="item.status === 'selesai'" class="px-3 py-1 bg-teal-500/10 text-teal-300 border border-teal-500/20 rounded-full text-xs font-bold">Selesai</span>
                          </div>
                        </div>
                      </template>
                    </template>
                  </div>
                </div>

              </div>

            </div>
          </div>

        </div>
      </template>
    </div>

    <!-- Modal CRUD Berita & Agenda -->
    <div x-show="showNewsModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md animate-in fade-in duration-300"
         style="display: none;">
      <div class="bg-slate-900/90 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] w-full max-w-lg p-8 shadow-2xl relative"
           @click.away="showNewsModal = false">
        
        <button @click="showNewsModal = false" class="absolute top-6 right-6 p-2 rounded-xl bg-white/5 text-white/70 hover:bg-white/10 transition-colors">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <h3 class="text-2xl font-black text-white mb-6" x-text="editingNewsId ? 'Ubah Berita / Agenda' : 'Tulis Berita / Agenda Baru'"></h3>

        <form @submit.prevent="handleKirimNews()" class="space-y-5">
          <div>
            <label class="block text-sm font-bold text-white mb-2 ml-1">Tipe Informasi</label>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" 
                      @click="formNews.type = 'berita'"
                      class="py-3 rounded-xl text-xs font-bold transition-all border"
                      :class="formNews.type === 'berita' ? 'bg-emerald-600 text-white border-emerald-500 shadow-md' : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10'">
                Berita Desa
              </button>
              <button type="button" 
                      @click="formNews.type = 'agenda'"
                      class="py-3 rounded-xl text-xs font-bold transition-all border"
                      :class="formNews.type === 'agenda' ? 'bg-emerald-600 text-white border-emerald-500 shadow-md' : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10'">
                Agenda Kegiatan
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-white mb-2 ml-1">Judul Informasi</label>
            <input type="text" 
                   x-model="formNews.title"
                   class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner"
                   placeholder="Masukkan judul kabar/kegiatan..." />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-white mb-2 ml-1">Tanggal Rilis / Pelaksanaan</label>
              <input type="text" 
                     x-model="formNews.date"
                     class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner"
                     placeholder="Contoh: 28 Mei 2026" />
            </div>
            <div>
              <label class="block text-sm font-bold text-white mb-2 ml-1">Kategori Tag</label>
              <div class="relative">
                <select x-model="formNews.tag"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-bold focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner appearance-none cursor-pointer">
                  <option class="bg-slate-950 text-white">Berita</option>
                  <option class="bg-slate-950 text-white">Agenda</option>
                  <option class="bg-slate-950 text-white">Pembangunan</option>
                  <option class="bg-slate-950 text-white">Pengumuman</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-white/50">
                  <i data-lucide="chevron-down" class="w-5 h-5"></i>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-white mb-2 ml-1">Konten / Deskripsi Informasi</label>
            <textarea x-model="formNews.description"
                      class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white font-semibold placeholder-white/20 focus:outline-none focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-400/40 transition-all shadow-inner h-32 resize-none animate-in fade-in duration-300"
                      placeholder="Masukkan detail informasi, kronologi, atau detail agenda..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-bold text-white mb-2 ml-1">Unggah Foto Sampul (Opsional)</label>
            <div class="relative bg-white/5 border border-white/10 rounded-2xl px-5 py-4 flex items-center gap-3 shadow-inner hover:bg-white/10 transition-colors">
              <i data-lucide="image" class="w-5 h-5 text-emerald-400 shrink-0"></i>
              <input type="file" 
                     id="foto-news"
                     accept="image/*"
                     @change="formNews.fotoFile = $event.target.files[0]"
                     class="w-full text-sm font-bold text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-white/10 file:text-white hover:file:bg-white/20 file:cursor-pointer cursor-pointer" />
            </div>
            <p class="text-[10px] text-white/40 mt-1.5 ml-1 font-semibold">Maks. 2MB. Kosongkan untuk menggunakan gambar default.</p>
          </div>

          <button type="submit" 
                  class="w-full bg-emerald-600 text-white font-bold rounded-2xl px-5 py-4 hover:bg-emerald-500 hover:shadow-xl hover:shadow-emerald-950/20 hover:-translate-y-1 transition-all flex justify-center items-center mt-4">
            <span x-text="editingNewsId ? 'Simpan Perubahan' : 'Terbitkan Sekarang'"></span>
          </button>
        </form>
      </div>
    </div>

  </main>

  <!-- Footer -->
  <footer class="relative bg-slate-950/50 backdrop-blur-lg border-t border-white/[0.08] py-16 overflow-hidden">
    <!-- Footer Ambient Glow Orbs -->
    <div class="absolute -bottom-24 -left-20 w-[400px] h-[400px] bg-violet-600/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-20 w-[400px] h-[400px] bg-cyan-600/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-10">
      <div class="md:col-span-2 space-y-6">
        <div class="flex items-center gap-3">
          <div class="relative w-10 h-10 bg-gradient-to-br from-violet-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg border border-violet-400/20">
            <i data-lucide="leaf" class="w-5 h-5"></i>
          </div>
          <div>
            <span class="block font-black text-white text-xl tracking-tight leading-none">SIPUHJARAK</span>
            <span class="block text-[9px] font-bold text-violet-400/60 uppercase tracking-[0.18em] mt-0.5">Portal Desa Digital</span>
          </div>
        </div>
        <p class="text-sm font-medium leading-relaxed max-w-md text-white/50">
          Sistem Informasi & Pelayanan Desa Puhjarak. Menghadirkan kemudahan akses informasi publik, transparansi pembangunan, dan partisipasi aktif warga demi kemajuan desa.
        </p>
      </div>
      <div>
        <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Layanan Digital</h4>
        <ul class="space-y-2.5 text-sm font-medium">
          <li><button @click="activeTab = 'pengaduan'; window.scrollTo(0,0);" class="text-white/50 hover:text-white transition-colors text-left flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-violet-400 opacity-0 group-hover:opacity-100 transition-opacity"></span>E-Aduan Aspirasi</button></li>
          <li><button @click="activeTab = 'berita'; window.scrollTo(0,0);" class="text-white/50 hover:text-white transition-colors text-left flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"></span>Informasi & Agenda</button></li>
          <li><button @click="activeTab = 'profil'; window.scrollTo(0,0);" class="text-white/50 hover:text-white transition-colors text-left flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-pink-400 opacity-0 group-hover:opacity-100 transition-opacity"></span>Profil Wilayah</button></li>
          <li><button @click="activeTab = 'login'; window.scrollTo(0,0);" class="text-white/50 hover:text-white transition-colors text-left flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity"></span>Portal Warga</button></li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Kontak Kami</h4>
        <ul class="space-y-3.5 text-sm font-medium text-white/50">
          <li class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-white/[0.04] border border-white/10 flex items-center justify-center shrink-0">
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-violet-400"></i>
            </div>
            <span class="leading-relaxed">Jl. Raya Puhjarak No. 1, Kec. Puncu, Kabupaten Kediri, Jawa Timur</span>
          </li>
          <li class="flex items-center gap-3">
            <div class="w-6 h-6 rounded-lg bg-white/[0.04] border border-white/10 flex items-center justify-center shrink-0">
              <i data-lucide="mail" class="w-3.5 h-3.5 text-cyan-400"></i>
            </div>
            <span>info@puhjarak.desa.id</span>
          </li>
        </ul>
      </div>
    </div>
    <div class="max-w-7xl mx-auto px-6 border-t border-white/[0.06] mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-xs font-semibold text-white/30 uppercase tracking-wider">&copy; <span x-text="new Date().getFullYear()"></span> Pemerintah Desa Puhjarak. All rights reserved.</p>
      <p class="text-xs font-semibold text-white/30 flex items-center gap-2 uppercase tracking-wider"><i data-lucide="shield" class="w-3.5 h-3.5 text-violet-400"></i> Keterbukaan Informasi Publik</p>
    </div>
  </footer>

</body>
</html>
