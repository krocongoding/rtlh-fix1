@props(['title' => 'Portal RTLH Cirebon'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Portal RTLH Cirebon') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')

    {{-- Anti-FOUC: apply dark class before first paint --}}
    <script>if(localStorage.getItem('rtlh-dark-mode')==='1'){document.documentElement.classList.add('dark');}</script>
</head>
<body>
    {{-- BACKDROP OVERLAY FOR DRAWER --}}
    <div id="drawer-overlay" class="drawer-overlay" onclick="closeNavDrawer()"></div>

    {{-- LEFT SLIDING DRAWER MENU --}}
    <aside id="nav-drawer" class="nav-drawer" aria-label="Menu Navigasi">
        <div class="drawer-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div class="brand-logo" style="width:34px; height:34px; font-size:16px;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                </div>
                <div style="font-weight:800; font-size:15px; color:#fff;">
                    Menu Portal RTLH
                </div>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closeNavDrawer()" title="Tutup Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="drawer-body">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <div>
                        <div class="drawer-section-title">ADMINISTRATOR WORKSPACE</div>
                        <div class="drawer-nav-list">
                            <a href="{{ route('admin.dashboard') }}" class="drawer-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                            </a>
                            <a href="{{ route('houses.index') }}" class="drawer-nav-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-folder-tree"></i> Data RTLH
                            </a>
                            <a href="{{ route('admin.review.index') }}" class="drawer-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clipboard-check"></i> Review & Approval
                            </a>
                            <a href="{{ route('admin.surveyors.index') }}" class="drawer-nav-link {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-users-gear"></i> Kelola Surveyor
                            </a>
                            <a href="{{ route('regions.index') }}" class="drawer-nav-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-map-pin"></i> Wilayah Administratif
                            </a>
                        </div>
                    </div>
                @elseif(auth()->user()->hasRole('surveyor'))
                    <div>
                        <div class="drawer-section-title">SURVEYOR WORKSPACE</div>
                        <div class="drawer-nav-list">
                            <a href="{{ route('surveyor.dashboard') }}" class="drawer-nav-link {{ request()->routeIs('surveyor.dashboard') ? 'active' : '' }}">
                                <i class="fa-solid fa-gauge"></i> Workspace Surveyor
                            </a>
                            <a href="{{ route('houses.index') }}" class="drawer-nav-link {{ request()->routeIs('houses.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-list-check"></i> Data RTLH Saya
                            </a>
                            <a href="{{ route('surveyor.rtlh.create') }}" class="drawer-nav-link {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                                <i class="fa-solid fa-plus-circle"></i> Input RTLH Baru
                            </a>
                        </div>
                    </div>
                @endif
            @endauth

            <div>
                <div class="drawer-section-title">PORTAL PUBLIK & GIS</div>
                <div class="drawer-nav-list">
                    <a href="{{ route('public.home') }}" class="drawer-nav-link {{ request()->routeIs('public.home') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i> Beranda Publik
                    </a>
                    <a href="{{ route('public.map') }}" class="drawer-nav-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                        <i class="fa-solid fa-map-location-dot"></i> Peta GIS Tematik
                    </a>
                    <a href="{{ route('public.statistics') }}" class="drawer-nav-link {{ request()->routeIs('public.statistics') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Statistik & Grafik
                    </a>
                    <a href="{{ route('public.datasets') }}" class="drawer-nav-link {{ request()->routeIs('public.datasets') ? 'active' : '' }}">
                        <i class="fa-solid fa-database"></i> Katalog Open Data
                    </a>
                </div>
            </div>

            @guest
                <div style="margin-top:auto; padding-top:16px;">
                    <a href="{{ route('login') }}" class="btn primary" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Petugas
                    </a>
                </div>
            @endguest
        </div>

        @auth
            <div class="drawer-footer">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="user-avatar" style="width:36px; height:36px; font-size:14px;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="overflow:hidden;">
                        <div style="font-weight:700; font-size:13px; color:#fff; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">
                            {{ auth()->user()->name }}
                        </div>
                        <div style="font-size:11px; color:#94a3b8;">
                            {{ auth()->user()->email }}
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </aside>

    {{-- Top Navigation Bar --}}
    <header class="topbar">
        <div class="container nav" style="display:flex; align-items:center; justify-content:space-between;">
            {{-- LEFT: DRAWER MENU TOGGLE + BRAND --}}
            <div style="display:flex; align-items:center; gap:14px;">
                <button type="button" class="nav-drawer-btn" onclick="toggleNavDrawer()" title="Buka Menu Navigasi">
                    <i class="fa-solid fa-bars"></i>
                    <span>Menu</span>
                </button>

                <a href="{{ auth()->check() ? route('dashboard') : route('public.home') }}" class="brand-wrapper">
                    <div class="brand-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                    </div>
                    <div class="brand-info">
                        <span class="brand-title">RTLH Cirebon</span>
                        <span class="brand-subtitle">Sistem Informasi Geografis</span>
                    </div>
                </a>
            </div>

            {{-- RIGHT: PROFILE & ACTIONS (KEPT IN TOPBAR) --}}
            <div style="display:flex; align-items:center; gap:12px;">
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
                    <a href="{{ route('login') }}" class="nav-link nav-btn-login">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i> Masuk Petugas
                    </a>
                @endauth

                {{-- DARK MODE TOGGLE --}}
                <button type="button" class="btn-theme-toggle" onclick="toggleDarkMode()" id="darkToggleBtn" title="Ganti tema">
                    <i class="fa-solid fa-moon" id="darkToggleIcon"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="container fade-in" style="flex:1; padding-top:20px; padding-bottom:48px;">
        @isset($header)
            <div class="pagehead" style="padding-top:10px; padding-bottom:18px;">
                {{ $header }}
            </div>
        @endisset

        {{-- FLASH MESSAGES --}}
        @if(session('ok') || session('success'))
            <div class="alert ok">
                <i class="fa-solid fa-circle-check fa-lg"></i>
                <div>{{ session('ok') ?? session('success') }}</div>
            </div>
        @endif

        @if(session('error') || session('err'))
            <div class="alert err">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <div>{{ session('error') ?? session('err') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container" style="text-align:center;">
            <div>&copy; {{ date('Y') }} Pemerintah Kabupaten Cirebon • Dinas Perumahan, Kawasan Permukiman dan Pertanahan</div>
        </div>
    </footer>

    <script>
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

        applyDarkMode(localStorage.getItem(DARK_KEY) === '1');

        // ── Drawer Menu (Non-blocking Offcanvas) ────────────
        function toggleNavDrawer() {
            const drawer = document.getElementById('nav-drawer');
            if (drawer) {
                drawer.classList.toggle('open');
            }
        }

        function closeNavDrawer() {
            const drawer = document.getElementById('nav-drawer');
            if (drawer) {
                drawer.classList.remove('open');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
