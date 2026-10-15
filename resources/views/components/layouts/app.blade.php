@props([
    'title' => 'Workspace Petugas | RTLH Cirebon',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Workspace Petugas RTLH Kabupaten Cirebon">
    <meta name="theme-color" content="#0f172a">

    <title>{{ $title }} | RTLH Kabupaten Cirebon</title>

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

    {{-- TOPBAR NAVIGATION — WORKSPACE PETUGAS --}}
    <header class="topbar">
        <div class="container nav">

            {{-- LEFT: LOGO & BRANDING --}}
            <a href="{{ route('dashboard') }}" class="brand-wrapper" title="Menuju Dashboard">
                <div class="lapa-brand-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                </div>
                <div class="brand-info">
                    <span class="brand-title">
                        rtlh<span style="color:#38bdf8;">.</span>cirebon
                    </span>
                    <span class="brand-subtitle" style="color:#38bdf8; display:flex; align-items:center; gap:4px; font-weight:700;">
                        <i class="fa-solid fa-shield-halved" style="font-size:9px;"></i>
                        Workspace Petugas
                    </span>
                </div>
            </a>

            {{-- CENTER: WORKSPACE NAVIGATION LINKS (ADMIN & SURVEYOR) --}}
            <nav class="nav-center">
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="lapa-nav-link {{ request()->routeIs('admin.dashboard') || (request()->routeIs('dashboard') && auth()->user()->hasRole('admin')) ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high" style="font-size:12px; opacity:0.8;"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('houses.index') }}" class="lapa-nav-link {{ request()->routeIs('houses.index') || (request()->routeIs('houses.*') && !request()->routeIs('houses.create')) ? 'active' : '' }}">
                            <i class="fa-solid fa-house-chimney" style="font-size:12px; opacity:0.8;"></i>
                            <span>Data RTLH</span>
                        </a>

                        <a href="{{ route('houses.create') }}" class="lapa-nav-link {{ request()->routeIs('houses.create') ? 'active' : '' }}">
                            <i class="fa-solid fa-plus-circle" style="font-size:12px; opacity:0.8;"></i>
                            <span>Input Baru</span>
                        </a>

                        <a href="{{ route('admin.review.index') }}" class="lapa-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-check" style="font-size:12px; opacity:0.8;"></i>
                            <span>Verifikasi</span>
                        </a>

                        <a href="{{ route('admin.surveyors.index') }}" class="lapa-nav-link {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear" style="font-size:12px; opacity:0.8;"></i>
                            <span>Surveyor</span>
                        </a>

                        <a href="{{ route('regions.index') }}" class="lapa-nav-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-map-location-dot" style="font-size:12px; opacity:0.8;"></i>
                            <span>Wilayah</span>
                        </a>
                    @elseif(auth()->user()->hasRole('surveyor'))
                        <a href="{{ route('surveyor.dashboard') }}" class="lapa-nav-link {{ request()->routeIs('surveyor.dashboard') || (request()->routeIs('dashboard') && auth()->user()->hasRole('surveyor')) ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge" style="font-size:12px; opacity:0.8;"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('houses.index') }}" class="lapa-nav-link {{ request()->routeIs('houses.index') || (request()->routeIs('houses.*') && !request()->routeIs('surveyor.rtlh.create')) ? 'active' : '' }}">
                            <i class="fa-solid fa-folder-tree" style="font-size:12px; opacity:0.8;"></i>
                            <span>Data RTLH</span>
                        </a>

                        <a href="{{ route('surveyor.rtlh.create') }}" class="lapa-nav-link {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                            <i class="fa-solid fa-plus-circle" style="font-size:12px; opacity:0.8;"></i>
                            <span>Input RTLH</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="lapa-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high" style="font-size:12px; opacity:0.8;"></i>
                            <span>Dashboard</span>
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- RIGHT: SHORTCUT TO PUBLIC WEB, USER PROFILE, AND CONTROLS --}}
            <div style="display:flex; align-items:center; gap:12px;">

                {{-- SHORTCUT TO PUBLIC SITE --}}
                <a href="{{ route('public.home') }}" target="_blank" class="lapa-btn-public-portal" title="Buka Portal Publik Warga di Tab Baru">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                    <span>Lihat Web Publik</span>
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
                            @else
                                <span class="role-pill">Petugas</span>
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

        {{-- MOBILE COLLAPSIBLE DROPDOWN MENU FOR WORKSPACE --}}
        <div id="lapaMobileMenu" class="lapa-mobile-menu">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="lapa-mobile-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                    </a>
                    <a href="{{ route('houses.index') }}" class="lapa-mobile-link {{ request()->routeIs('houses.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-house-chimney"></i> Basis Data RTLH
                    </a>
                    <a href="{{ route('houses.create') }}" class="lapa-mobile-link {{ request()->routeIs('houses.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i> Input RTLH Baru
                    </a>
                    <a href="{{ route('admin.review.index') }}" class="lapa-mobile-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check"></i> Review & Verifikasi
                    </a>
                    <a href="{{ route('admin.surveyors.index') }}" class="lapa-mobile-link {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i> Kelola Surveyor
                    </a>
                    <a href="{{ route('regions.index') }}" class="lapa-mobile-link {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-map-location-dot"></i> Wilayah Administratif
                    </a>
                @elseif(auth()->user()->hasRole('surveyor'))
                    <a href="{{ route('surveyor.dashboard') }}" class="lapa-mobile-link {{ request()->routeIs('surveyor.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge"></i> Workspace Surveyor
                    </a>
                    <a href="{{ route('houses.index') }}" class="lapa-mobile-link {{ request()->routeIs('houses.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-folder-tree"></i> Data RTLH Saya
                    </a>
                    <a href="{{ route('surveyor.rtlh.create') }}" class="lapa-mobile-link {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i> Input RTLH Baru
                    </a>
                @endif

                <div style="padding-top:10px; border-top:1px solid rgba(255,255,255,0.08); margin-top:8px;">
                    <a href="{{ route('public.home') }}" target="_blank" class="lapa-mobile-link" style="color:#38bdf8;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Portal Publik
                    </a>
                    <a href="{{ route('profile.edit') }}" class="lapa-mobile-link">
                        <i class="fa-solid fa-user-gear"></i> Pengaturan Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="margin-top:6px;">
                        @csrf
                        <button type="submit" class="lapa-mobile-link" style="width:100%; background:none; border:none; text-align:left; cursor:pointer; color:#f87171;">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </button>
                    </form>
                </div>
            @else
                <div style="padding-top:10px;">
                    <a href="{{ route('login') }}" class="lapa-btn-signin" style="width:100%; justify-content:center;">
                        Sign in / Masuk Petugas
                    </a>
                </div>
            @endauth
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

        @if(session('status'))
            <div class="alert info">
                <i class="fa-solid fa-circle-info fa-lg"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Backoffice Workspace Footer --}}
    <footer class="footer" style="padding: 24px 0; margin-top:auto;">
        <div class="container" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:13px; color:#64748b;">
            <div>
                &copy; {{ date('Y') }} <strong style="color:#cbd5e1;">Pemerintah Kabupaten Cirebon</strong> &bull; DPKPP Workspace Petugas
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                <a href="{{ route('public.home') }}" target="_blank" style="color:#38bdf8; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-globe"></i> Portal Publik
                </a>
                <a href="{{ route('profile.edit') }}" style="color:#94a3b8; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-user-gear"></i> Profil
                </a>
            </div>
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