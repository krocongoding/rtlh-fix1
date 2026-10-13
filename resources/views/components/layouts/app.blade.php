@props([
    'title' => 'Portal RTLH Kabupaten Cirebon',
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Data dan Sistem Informasi Geografis Rumah Tidak Layak Huni (RTLH) Kabupaten Cirebon">
    <meta name="theme-color" content="#0f172a">

    <title>{{ $title }} | Portal RTLH Kabupaten Cirebon</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome / Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')

    {{-- Anti-FOUC: apply dark class before first paint --}}
    <script>if(localStorage.getItem('rtlh-dark-mode')==='1'){document.documentElement.classList.add('dark');}</script>
</head>

<body>

    {{-- TOPBAR NAVIGATION — LAPA NINJA STYLE --}}
    <header class="topbar">
        <div class="container nav">

            {{-- LEFT: LOGO & BRANDING --}}
            <a href="{{ auth()->check() ? route('dashboard') : route('public.home') }}" class="brand-wrapper">
                <div class="lapa-brand-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                </div>
                <div class="brand-info">
                    <span class="brand-title">
                        rtlh<span style="color:#38bdf8;">.</span>cirebon
                    </span>
                    <span class="brand-subtitle">
                        Sistem Informasi Geografis
                    </span>
                </div>
            </a>

            {{-- CENTER: HORIZONTAL LINKS (Role-based Navigation) --}}
            <nav class="nav-center">
                {{-- Dropdown: WebGIS (Semua role & publik) --}}
                <div class="lapa-dropdown">
                    <a href="{{ route('public.map') }}" class="lapa-nav-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                        <span>WebGIS</span>
                        <i class="fa-solid fa-chevron-down" style="font-size:10px; opacity:0.7;"></i>
                    </a>
                    <div class="lapa-dropdown-menu">
                        <a href="{{ route('public.map') }}" class="lapa-dropdown-item">
                            <i class="fa-solid fa-map-location-dot" style="color:#38bdf8; width:16px;"></i>
                            <span>Peta GIS Tematik</span>
                        </a>
                        <a href="{{ route('public.map') }}#heatmap" class="lapa-dropdown-item">
                            <i class="fa-solid fa-fire-flame-curved" style="color:#f43f5e; width:16px;"></i>
                            <span>Heatmap Kerentanan</span>
                        </a>
                    </div>
                </div>

                {{-- Khusus Admin & Surveyor: Data RTLH dan Wilayah --}}
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('houses.index') }}" class="lapa-nav-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                            Data RTLH
                        </a>
                        <a href="{{ route('admin.review.index') }}" class="lapa-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                            Verifikasi
                        </a>
                        <a href="{{ route('admin.surveyors.index') }}" class="lapa-nav-link {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                            Surveyor
                        </a>
                        <a href="{{ route('regions.index') }}" class="lapa-nav-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                            Wilayah
                        </a>
                    @elseif(auth()->user()->hasRole('surveyor'))
                        <a href="{{ route('houses.index') }}" class="lapa-nav-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                            Data RTLH
                        </a>
                        <a href="{{ route('surveyor.rtlh.create') }}" class="lapa-nav-link {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                            Input Baru
                        </a>
                    @endif
                @endauth

                {{-- Menu Terbuka untuk Publik & Semua Pengguna --}}
                <a href="{{ route('public.statistics') }}" class="lapa-nav-link {{ request()->routeIs('public.statistics') ? 'active' : '' }}">
                    Statistik
                </a>

                <a href="{{ route('public.datasets') }}" class="lapa-nav-link {{ request()->routeIs('public.datasets') ? 'active' : '' }}">
                    Unduh CSV
                </a>
            </nav>

            {{-- RIGHT: SEARCH PILL, PROFILE / SIGN IN, AND DARK TOGGLE --}}
            <div style="display:flex; align-items:center; gap:12px;">

                {{-- Search Pill (Lapa style) --}}
                <a href="{{ route('public.datasets') }}" class="lapa-search-pill" title="Cari data RTLH">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:12px;"></i>
                    <span class="search-text">Search...</span>
                    <span class="search-badge">⌘K</span>
                </a>

                @auth
                    <div class="user-profile-badge">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            @if(auth()->user()->hasRole('admin'))
                                <span class="role-pill role-admin">Admin</span>
                            @elseif(auth()->user()->hasRole('surveyor'))
                                <span class="role-pill role-surveyor">Surveyor</span>
                            @elseif(auth()->user()->hasRole('viewer'))
                                <span class="role-pill role-viewer">Viewer</span>
                            @else
                                <span class="role-pill">User</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="nav-link" title="Pengaturan Profil">
                        <i class="fa-solid fa-user-gear"></i>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;" title="Keluar">
                            <i class="fa-solid fa-right-from-bracket text-red-400"></i>
                        </button>
                    </form>
                @else
                    {{-- Vibrant Purple Pill Button (Lapa Sign In) --}}
                    <a href="{{ route('login') }}" class="lapa-btn-signin">
                        Sign in
                    </a>
                @endauth

                {{-- DARK MODE TOGGLE --}}
                <button type="button" class="btn-theme-toggle" onclick="toggleDarkMode()" id="darkToggleBtn" title="Ganti tema">
                    <i class="fa-solid fa-moon" id="darkToggleIcon"></i>
                </button>

                {{-- MOBILE HAMBURGER TOGGLE --}}
                <button type="button" class="lapa-mobile-toggle" onclick="toggleMobileNav()" title="Buka Menu Navigasi">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

        </div>

        {{-- MOBILE COLLAPSIBLE DROPDOWN MENU --}}
        <div id="lapaMobileMenu" class="lapa-mobile-menu">
            <a href="{{ route('public.home') }}" class="lapa-mobile-link {{ request()->routeIs('public.home') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> Beranda Publik
            </a>
            <a href="{{ route('public.map') }}" class="lapa-mobile-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                <i class="fa-solid fa-map-location-dot"></i> Peta WebGIS Tematik
            </a>
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="lapa-mobile-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                    </a>
                    <a href="{{ route('houses.index') }}" class="lapa-mobile-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-folder-tree"></i> Basis Data RTLH
                    </a>
                    <a href="{{ route('admin.review.index') }}" class="lapa-mobile-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check"></i> Review & Approval
                    </a>
                    <a href="{{ route('admin.surveyors.index') }}" class="lapa-mobile-link {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i> Kelola Surveyor
                    </a>
                    <a href="{{ route('regions.index') }}" class="lapa-mobile-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-map-pin"></i> Wilayah Administratif
                    </a>
                @elseif(auth()->user()->hasRole('surveyor'))
                    <a href="{{ route('surveyor.dashboard') }}" class="lapa-mobile-link {{ request()->routeIs('surveyor.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge"></i> Workspace Surveyor
                    </a>
                    <a href="{{ route('houses.index') }}" class="lapa-mobile-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-folder-tree"></i> Data RTLH Saya
                    </a>
                    <a href="{{ route('surveyor.rtlh.create') }}" class="lapa-mobile-link {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i> Input RTLH Baru
                    </a>
                @endif
            @endauth
            <a href="{{ route('public.statistics') }}" class="lapa-mobile-link {{ request()->routeIs('public.statistics') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Statistik & Grafik
            </a>
            <a href="{{ route('public.datasets') }}" class="lapa-mobile-link {{ request()->routeIs('public.datasets') ? 'active' : '' }}">
                <i class="fa-solid fa-database"></i> Katalog Open Data
            </a>
            @guest
                <div style="padding-top:10px;">
                    <a href="{{ route('login') }}" class="lapa-btn-signin" style="width:100%; justify-content:center;">
                        Sign in / Masuk Petugas
                    </a>
                </div>
            @endguest
        </div>
    </header>

    {{-- MAIN CONTENT WRAPPER --}}
    <main class="container fade-in" style="flex:1; padding-top:12px; padding-bottom:48px;">
        {{-- FLASH MESSAGES --}}
        @if(session('ok') || session('success'))
            <div class="alert ok" style="margin-top:16px;">
                <i class="fa-solid fa-circle-check fa-lg"></i>
                <div>{{ session('ok') ?? session('success') }}</div>
            </div>
        @endif

        @if(session('error') || session('err'))
            <div class="alert err" style="margin-top:16px;">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <div>{{ session('error') ?? session('err') }}</div>
            </div>
        @endif

        @if(session('status'))
            <div class="alert info" style="margin-top:16px;">
                <i class="fa-solid fa-circle-info fa-lg"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <div class="brand-logo" style="width:36px;height:36px;font-size:16px;">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                        </div>
                        <h4 style="margin:0;">Portal RTLH Cirebon</h4>
                    </div>
                    <p>Sistem Informasi Geografis dan Basis Data Terpadu Penanganan Rumah Tidak Layak Huni (RTLH) Kabupaten Cirebon.</p>
                </div>

                <div class="footer-col">
                    <h5>Navigasi Publik</h5>
                    <ul>
                        <li><a href="{{ route('public.home') }}">Beranda</a></li>
                        <li><a href="{{ route('public.map') }}">Peta GIS Tematik</a></li>
                        <li><a href="{{ route('public.statistics') }}">Statistik & Tren</a></li>
                        <li><a href="{{ route('public.datasets') }}">Katalog Open Data</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Layanan & Integrasi</h5>
                    <ul>
                        <li><a href="{{ route('api.public.rtlh') }}" target="_blank">RTLH GeoJSON API</a></li>
                        <li><a href="{{ route('api.public.statistics') }}" target="_blank">Statistik JSON API</a></li>
                        <li><a href="{{ route('public.download.rtlh') }}">Download CSV RTLH</a></li>
                        <li><a href="{{ route('public.viewer') }}">Panduan Pengguna</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Akses Petugas</h5>
                    <ul>
                        @auth
                            <li><a href="{{ route('dashboard') }}">Dashboard Saya</a></li>
                            <li><a href="{{ route('profile.edit') }}">Pengaturan Akun</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Login Petugas Lapangan</a></li>
                            <li><a href="{{ route('register') }}">Registrasi Surveyor</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} Pemerintah Kabupaten Cirebon. Dinas Perumahan, Kawasan Permukiman dan Pertanahan.</div>
                <div>WebGIS & Data Platform v2.0</div>
            </div>
        </div>
    </footer>

    <script>
        // ── Dark Mode ─────────────────────────────────────────
        const DARK_KEY = 'rtlh-dark-mode';
        const html     = document.documentElement;
        const icon     = document.getElementById('darkToggleIcon');

        function applyDarkMode(dark) {
            if (dark) {
                html.classList.add('dark');
                if (icon) icon.className = 'fa-solid fa-sun';
            } else {
                html.classList.remove('dark');
                if (icon) icon.className = 'fa-solid fa-moon';
            }
            localStorage.setItem(DARK_KEY, dark ? '1' : '0');
        }

        function toggleDarkMode() {
            applyDarkMode(!html.classList.contains('dark'));
        }

        // Apply saved preference immediately (prevents flash)
        applyDarkMode(localStorage.getItem(DARK_KEY) === '1');

        // ── Mobile Dropdown Menu Toggle ───────────────────
        function toggleMobileNav() {
            const menu = document.getElementById('lapaMobileMenu');
            if (menu) {
                menu.classList.toggle('open');
            }
        }
    </script>

    @stack('scripts')
</body>

</html>