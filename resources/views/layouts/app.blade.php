<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f97316">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon-180.png') }}">

    <title>@yield('title', 'Beranda') &middot; KotakRasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-kr-cream font-sans text-kr-ink antialiased">
    @php
        $pengguna = auth()->user();
        $menuBawah = [
            ['route' => 'dashboard', 'cocok' => 'dashboard*', 'label' => 'Beranda', 'icon' => 'home'],
            ['route' => 'pesanan.index', 'cocok' => 'pesanan.*', 'label' => 'Pesanan', 'icon' => 'bag'],
            ['route' => 'jadwal.index', 'cocok' => 'jadwal.*', 'label' => 'Jadwal', 'icon' => 'calendar'],
            ['route' => 'pengeluaran.index', 'cocok' => 'pengeluaran.*', 'label' => 'Pengeluaran', 'icon' => 'wallet'],
        ];

        if ($pengguna?->isPemilik()) {
            $menuBawah[] = ['route' => 'laporan.index', 'cocok' => 'laporan.*', 'label' => 'Laporan', 'icon' => 'chart'];
        }
    @endphp

    {{-- Navigasi samping untuk layar besar --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-kr-line bg-white px-4 py-5 lg:flex"
           aria-label="Navigasi desktop">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-kr-primary text-white shadow-kr-primary/30">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5v9L12 21l9-4.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 12l9-4.5M12 12v9" />
                </svg>
            </span>
            <span class="leading-tight">
                <span class="block text-sm font-bold tracking-tight text-kr-ink">KotakRasa</span>
                <span class="block text-[9px] font-bold uppercase tracking-[0.14em] text-kr-faint">Panel Usaha</span>
            </span>
        </a>

        <p class="mt-8 px-3 text-[10px] font-bold uppercase tracking-[0.14em] text-kr-faint">Menu utama</p>

        <nav class="mt-2 space-y-1" aria-label="Navigasi samping">
            @foreach ($menuBawah as $menu)
                @php
                    $aktif = request()->routeIs($menu['cocok']);
                @endphp
                <a href="{{ route($menu['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $aktif ? 'bg-kr-tint text-kr-accent' : 'text-kr-muted hover:bg-kr-soft hover:text-kr-ink' }}"
                   @if ($aktif) aria-current="page" @endif>
                    <x-nav-icon :icon="$menu['icon']" class="shrink-0" />
                    <span>{{ $menu['label'] }}</span>
                    @if ($aktif)
                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-kr-primary"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-auto rounded-xl bg-kr-tint p-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-kr-faint">Akun aktif</p>
            <p class="mt-1 truncate text-sm font-semibold text-kr-ink">{{ $pengguna?->name }}</p>
            <p class="text-[11px] text-kr-faint">{{ $pengguna?->isPemilik() ? 'Pemilik' : 'Pencatat' }}</p>
        </div>

        <x-install-app kelas="mt-3" />
    </aside>

    <div class="min-h-screen lg:pl-64">
    {{-- Header --}}
    <header class="sticky top-0 z-30 border-b border-kr-line/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-md items-center justify-between gap-3 px-4 py-3 lg:max-w-6xl lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-kr-primary text-white shadow-kr-primary/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5v9L12 21l9-4.5v-9Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 12l9-4.5M12 12v9" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-bold tracking-tight text-kr-ink">KotakRasa</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-kr-faint">Katering &amp; Invoice</span>
                </span>
            </a>

            <div class="flex items-center gap-2">
                @if ($pengguna?->isPemilik())
                    <span class="rounded-full bg-kr-soft px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-kr-accent">Pemilik</span>
                @else
                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-700">Pencatat</span>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg border border-kr-line bg-white px-2.5 py-1.5 text-xs font-semibold text-kr-muted transition hover:border-rose-200 hover:bg-rose-50 hover:text-kr-danger"
                            title="Keluar dari aplikasi">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9m9 0-3-3m3 3-3 3" />
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main class="mx-auto w-full max-w-md px-4 pb-28 pt-5 lg:max-w-6xl lg:px-8 lg:pb-10 lg:pt-6">
        @if (session('success'))
            <div class="mb-4 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-kr-success-deep">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-kr-danger">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Navigasi bawah --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-kr-line bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden"
         aria-label="Navigasi utama">
        <div class="mx-auto flex max-w-md items-stretch px-1">
            @foreach ($menuBawah as $menu)
                @php
                    $aktif = request()->routeIs($menu['cocok']);
                @endphp
                <a href="{{ route($menu['route']) }}"
                   class="flex flex-1 flex-col items-center gap-1 px-1 py-2.5 text-[10px] font-semibold transition {{ $aktif ? 'text-kr-primary' : 'text-kr-faint hover:text-kr-muted' }}"
                   @if ($aktif) aria-current="page" @endif>
                    <x-nav-icon :icon="$menu['icon']" />
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    </div>

    @stack('scripts')
</body>
</html>
