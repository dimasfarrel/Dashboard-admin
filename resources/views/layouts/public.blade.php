<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cari dan sewa kost eksklusif di Malang dengan mudah, aman, dan cepat.">
    <title>@yield('title', 'Kost Malang') — Sewa Kost Mahasiswa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/public.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- Inline script: set dark class BEFORE paint to avoid flash --}}
    <script>
        (function() {
            const saved = localStorage.getItem('kost-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col">

    {{-- ===== TOP NAV (Desktop lg+) ===== --}}
    <header class="hidden lg:block sticky top-0 z-40 border-b" style="background:var(--background); border-color:var(--border);">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center gap-8">
            {{-- Logo --}}
            <a href="{{ route('public.home') }}" class="flex items-center gap-2 shrink-0" style="text-decoration:none;">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold" style="background:var(--primary);">K</div>
                <span class="font-bold text-lg" style="color:var(--foreground);">Kost<span style="color:var(--primary);">TBU4</span></span>
            </a>


            {{-- Nav links --}}
            <nav class="flex items-center gap-6 ml-auto">
                <a href="{{ route('public.home') }}" class="font-medium text-sm" style="color:var(--muted-foreground); text-decoration:none; {{ request()->routeIs('public.home') ? 'color:var(--primary)' : '' }}">Beranda</a>
                <a href="{{ route('public.rooms.index') }}" class="font-medium text-sm" style="color:var(--muted-foreground); text-decoration:none;">Kamar</a>

                @auth
                    @if(auth()->user()->role === 'tenant')
                        <a href="{{ route('public.tenant.dashboard') }}" class="font-medium text-sm" style="color:var(--muted-foreground); text-decoration:none;">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-ghost" style="color:var(--danger-text, #dc2626);">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-primary" style="height:36px; font-size:0.875rem;">Admin Panel</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="font-medium text-sm" style="color:var(--muted-foreground); text-decoration:none;">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary" style="height:36px; font-size:0.875rem;">Daftar</a>
                @endauth

                {{-- Toggle Tema --}}
                <button id="theme-toggle" type="button" aria-pressed="false" title="Toggle tema" class="btn-ghost" style="gap:6px; padding:0 10px; height:36px;">
                    <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <span id="theme-label" class="text-sm font-medium">Mode Gelap</span>
                </button>
            </nav>
        </div>
    </header>

    {{-- ===== MOBILE TOP BAR (< lg) ===== --}}
    <header class="lg:hidden sticky top-0 z-40 border-b h-14 flex items-center px-4 gap-3" style="background:var(--background); border-color:var(--border);">
        <a href="{{ route('public.home') }}" style="text-decoration:none; color:var(--foreground); font-weight:700; font-size:1.125rem; flex:1;">
            Kost<span style="color:var(--primary);">TBU4</span>
        </a>

        @auth
            @if(auth()->user()->role !== 'tenant')
                <a href="{{ route('dashboard') }}" class="btn-primary" style="height:32px; font-size:0.8125rem; padding:0 12px;">Admin</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn-ghost" style="height:32px; font-size:0.8125rem;">Masuk</a>
            <a href="{{ route('register') }}" class="btn-primary" style="height:32px; font-size:0.8125rem; padding:0 12px;">Daftar</a>
        @endauth

        {{-- Toggle tema mobile --}}
        <button id="theme-toggle-mobile" type="button" aria-pressed="false" title="Toggle tema" class="btn-ghost" style="width:36px; height:36px; padding:0;">
            <svg id="icon-sun-m" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
            <svg id="icon-moon-m" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>
    </header>

    {{-- Main Content --}}
    <main class="flex-grow pb-20 lg:pb-0" id="main-content">

        @if(session('success'))
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 p-4 rounded-xl" style="background:var(--success-bg); color:var(--success-text);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 p-4 rounded-xl" style="background:var(--danger-bg); color:var(--danger-text);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ===== BOTTOM TAB BAR Mobile (< lg) ===== --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 flex border-t" 
         style="background:var(--background); border-color:var(--border); padding-bottom:env(safe-area-inset-bottom);"
         aria-label="Navigasi utama">
        <a href="{{ route('public.home') }}" class="bottom-tab {{ request()->routeIs('public.home') ? 'active' : '' }}" aria-current="{{ request()->routeIs('public.home') ? 'page' : 'false' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="{{ request()->routeIs('public.home') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('public.rooms.index') }}" class="bottom-tab {{ request()->routeIs('public.rooms.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('public.rooms.*') ? 'page' : 'false' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span>Cari</span>
        </a>
        @auth
            @if(auth()->user()->role === 'tenant')
                <a href="{{ route('public.tenant.dashboard') }}" class="bottom-tab {{ request()->routeIs('public.tenant.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('public.tenant.*') ? 'page' : 'false' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    <span>Pesanan</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="flex:1;">
                    @csrf
                    <button type="submit" class="bottom-tab w-full" style="border:none; cursor:pointer; background:transparent;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <a href="{{ route('dashboard') }}" class="bottom-tab">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Admin</span>
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="bottom-tab {{ request()->routeIs('login') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profil</span>
            </a>
        @endauth
    </nav>

    {{-- Footer (desktop only) --}}
    <footer class="hidden lg:block border-t py-8" style="border-color:var(--border);">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background:var(--primary);">K</div>
                <span class="font-bold" style="color:var(--foreground);">Kost<span style="color:var(--primary);">TBU4</span></span>
            </div>
            <p class="text-sm" style="color:var(--muted-foreground);">&copy; {{ date('Y') }} KostTBU4. Hak cipta dilindungi.</p>
        </div>
    </footer>

    <script>
    // ===== TEMA TOGGLE =====
    function getTheme() { return localStorage.getItem('kost-theme'); }

    function applyTheme(dark) {
        if (dark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('kost-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('kost-theme', 'light');
        }
        updateToggleUI();
    }

    function updateToggleUI() {
        const isDark = document.documentElement.classList.contains('dark');

        // Desktop
        const sun = document.getElementById('icon-sun');
        const moon = document.getElementById('icon-moon');
        const label = document.getElementById('theme-label');
        const btn = document.getElementById('theme-toggle');
        if (sun && moon && label && btn) {
            sun.style.display  = isDark ? 'none' : '';
            moon.style.display = isDark ? '' : 'none';
            label.textContent  = isDark ? 'Mode Terang' : 'Mode Gelap';
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        }

        // Mobile
        const sunM  = document.getElementById('icon-sun-m');
        const moonM = document.getElementById('icon-moon-m');
        const btnM  = document.getElementById('theme-toggle-mobile');
        if (sunM && moonM && btnM) {
            sunM.style.display  = isDark ? 'none' : '';
            moonM.style.display = isDark ? '' : 'none';
            btnM.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateToggleUI();

        document.getElementById('theme-toggle')?.addEventListener('click', function () {
            applyTheme(!document.documentElement.classList.contains('dark'));
        });
        document.getElementById('theme-toggle-mobile')?.addEventListener('click', function () {
            applyTheme(!document.documentElement.classList.contains('dark'));
        });
    });
    </script>

    @stack('scripts')
</body>
</html>
