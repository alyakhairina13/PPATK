<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIM Akta Notaris & PPAT' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
</head>
<body class="h-screen overflow-hidden bg-surface text-on-surface">
    <div class="flex h-full flex-col">
        <!-- Topbar -->
        <header class="frosted-glass shrink-0 border-b border-border-hairline z-50">
            <div class="flex items-center justify-between px-4 py-2 sm:px-5">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="lg:hidden p-1 hover:bg-surface-pearl rounded-md">
                        <span class="material-symbols-outlined text-[20px]">menu</span>
                    </button>
                    <!-- Visual Brand Logo, very sleek -->
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-gradient-to-br from-blue-600 to-teal-600 flex items-center justify-center text-white font-bold text-[11px] tracking-wider shadow-sm">S</div>
                        <h1 class="text-xs font-bold tracking-wider text-slate-800 uppercase">SIM Akta Notaris & PPAT</h1>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    <button class="relative p-1 hover:bg-surface-pearl rounded-md text-slate-500 hover:text-slate-800">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span class="absolute top-1 right-1 w-1.5 h-1.5 bg-error rounded-full ring-2 ring-white"></span>
                    </button>
                    
                    <div class="h-4 w-px bg-black/10"></div>
                    
                    <!-- Compact User Profile Info with Dropdown -->
                    <div class="relative">
                        <button onclick="toggleUserDropdown()" class="flex items-center gap-2.5 p-1 rounded-md hover:bg-black/5 transition-all text-left">
                            <div class="text-right hidden sm:block">
                                <p class="font-semibold text-xs leading-none text-slate-800">{{ auth()->user()->nama_lengkap }}</p>
                                <p class="text-[9px] text-text-muted mt-0.5 font-medium leading-none">{{ auth()->user()->role }}</p>
                            </div>
                            <div class="w-7 h-7 bg-primary text-white rounded-md flex items-center justify-center font-semibold text-xs shadow-sm border border-white/20">
                                {{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 1)) }}
                            </div>
                            <span class="material-symbols-outlined text-[16px] text-slate-400">keyboard_arrow_down</span>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="user-dropdown" class="hidden absolute right-0 mt-1.5 w-44 bg-white/95 backdrop-blur-md border border-black/5 rounded-lg shadow-lg py-1 z-50">
                            <!-- Logout Button Trigger -->
                            <button type="button" onclick="confirmLogout()" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 text-left">
                                <span class="material-symbols-outlined text-[16px]">logout</span> Keluar
                            </button>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-1 min-h-0 overflow-hidden">
            <!-- Sidebar -->
            <aside id="sidebar" class="glass-sidebar fixed left-0 top-[49px] bottom-0 z-40 w-52 shrink-0 -translate-x-full transform overflow-y-auto transition-transform duration-300 ease-in-out lg:relative lg:top-auto lg:bottom-auto lg:mt-0 lg:h-full lg:translate-x-0 lg:overflow-y-auto">
                <div class="p-2.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest px-4.5 pt-4 pb-1.5">Menu Utama</div>
                <nav class="space-y-0.5 p-2">
                    @if(auth()->user()->isNotaris())
                    <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">home</span>
                        Dashboard
                    </a>
                    @endif

                    <a href="{{ route('klien.index') }}" class="sidebar-item {{ request()->routeIs('klien.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">groups</span>
                        Data Klien
                    </a>

                    <a href="{{ route('akta.index') }}" class="sidebar-item {{ request()->routeIs('akta.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">description</span>
                        Akta
                    </a>

                    <a href="{{ route('repertorium.index') }}" class="sidebar-item {{ request()->routeIs('repertorium.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">menu_book</span>
                        Repertorium
                    </a>

                    @if(auth()->user()->isNotaris())
                    <div class="p-2.5 text-[9px] font-bold text-slate-400 uppercase tracking-widest px-4.5 pt-5.5 pb-1.5">Administrasi</div>
                    
                    <a href="{{ route('laporan.index') }}" class="sidebar-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">analytics</span>
                        Laporan
                    </a>
                    @endif

                    @if(auth()->user()->isNotaris())
                    <a href="{{ route('konfigurasi.index') }}" class="sidebar-item {{ request()->routeIs('konfigurasi.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-[18px] mr-2">settings</span>
                        Konfigurasi
                    </a>
                    @endif
                </nav>

                <!-- Bottom Status -->
                <div class="absolute bottom-4 left-4 right-4 p-2.5 rounded-lg bg-slate-900/5 border border-black/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] font-semibold text-slate-600">Server Aktif</span>
                    </div>
                    <span class="text-[9px] text-slate-400 font-medium font-mono">v2.4</span>
                </div>
            </aside>

            <!-- Overlay for mobile -->
            <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>

            <!-- Main Content -->
            <main class="flex-1 min-h-0 min-w-0 overflow-y-auto p-4 sm:p-5">
                <!-- Breadcrumb -->
                @if(isset($breadcrumbs))
                <nav class="mb-4 text-sm">
                    <ol class="flex items-center space-x-2 text-text-muted">
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            @if($index > 0)
                                <li>
                                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                </li>
                            @endif
                            <li>
                                @if(isset($breadcrumb['url']))
                                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-primary">{{ $breadcrumb['label'] }}</a>
                                @else
                                    <span class="text-on-surface font-medium">{{ $breadcrumb['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
                @endif

                @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-3 py-2 rounded-md text-sm" role="alert">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-3 py-2 rounded-md text-sm" role="alert">
                    {{ session('error') }}
                </div>
                @endif

                <!-- Page Content -->
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 sm:p-6">
        <div class="card relative mx-4" style="width:min(28rem, calc(100vw - 2rem));">
            <button type="button" onclick="closeLogoutModal()" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full border border-border-hairline bg-surface-container-lowest text-text-muted hover:text-on-surface">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
            <div class="flex items-start gap-3 pr-10">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-error">
                    <span class="material-symbols-outlined text-[22px]">logout</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-on-surface">Konfirmasi Logout</h3>
                    <p class="mt-1 text-sm leading-6 text-text-muted">Apakah Anda yakin ingin keluar dari sistem?</p>
                </div>
            </div>
            <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeLogoutModal()" class="btn-secondary w-full sm:w-auto">Batal</button>
                <button type="button" onclick="document.getElementById('logout-form').submit()" class="btn-danger w-full sm:w-auto">Logout</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 sm:p-6">
        <div class="card relative mx-4" style="width:min(28rem, calc(100vw - 2rem));">
            <button type="button" onclick="closeDeleteModal()" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full border border-border-hairline bg-surface-container-lowest text-text-muted hover:text-on-surface">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
            <div class="flex items-start gap-3 pr-10">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-error">
                    <span class="material-symbols-outlined text-[22px]">warning</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-on-surface">Konfirmasi Hapus</h3>
                    <p class="mt-1 text-sm leading-6 text-text-muted">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeDeleteModal()" class="btn-secondary w-full sm:w-auto">Batal</button>
                <form id="deleteForm" method="POST" action="" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger w-full sm:w-auto">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // User dropdown menu toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) {
                const button = dropdown.previousElementSibling || dropdown.parentElement;
                if (button && !button.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        // Logout modal
        function confirmLogout() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }

        // Delete modal
        function confirmDelete(url) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeDeleteModal();
            }
        });

        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
