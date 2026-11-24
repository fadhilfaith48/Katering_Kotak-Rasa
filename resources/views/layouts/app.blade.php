<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ea580c">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>@yield('title', 'Beranda') &middot; KotakRasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    @php
        $pengguna = auth()->user();
        $menuBawah = [
            ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => 'home'],
            ['route' => 'pesanan.index', 'label' => 'Pesanan', 'icon' => 'bag'],
            ['route' => 'jadwal.index', 'label' => 'Jadwal', 'icon' => 'calendar'],
            ['route' => 'pengeluaran.index', 'label' => 'Pengeluaran', 'icon' => 'wallet'],
        ];

        if ($pengguna?->isPemilik()) {
            $menuBawah[] = ['route' => 'laporan.index', 'label' => 'Laporan', 'icon' => 'chart'];
        }
    @endphp

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-md items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-600 text-white shadow-sm shadow-orange-600/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5v9L12 21l9-4.5v-9Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 12l9-4.5M12 12v9" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-bold tracking-tight text-slate-900">KotakRasa</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Katering &amp; Invoice</span>
                </span>
            </a>

            <div class="flex items-center gap-2">
                @if ($pengguna?->isPemilik())
                    <span class="rounded-full bg-orange-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-orange-700">Pemilik</span>
                @else
                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-700">Pencatat</span>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
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
    <main class="mx-auto w-full max-w-md px-4 pb-28 pt-5">
        @if (session('success'))
            <div class="mb-4 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Navigasi bawah --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur"
         aria-label="Navigasi utama">
        <div class="mx-auto flex max-w-md items-stretch px-1">
            @foreach ($menuBawah as $menu)
                @php
                    $aktif = request()->routeIs($menu['route'].'*');
                @endphp
                <a href="{{ route($menu['route']) }}"
                   class="flex flex-1 flex-col items-center gap-1 px-1 py-2.5 text-[10px] font-semibold transition {{ $aktif ? 'text-orange-600' : 'text-slate-400 hover:text-slate-600' }}"
                   @if ($aktif) aria-current="page" @endif>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        @switch($menu['icon'])
                            @case('home')
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                @break
                            @case('bag')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                                @break
                            @case('calendar')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                @break
                            @case('wallet')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 1 3 7.5m18 4.5v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9v3m18 0h-5.25a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H21" />
                                @break
                            @case('chart')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                @break
                        @endswitch
                    </svg>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
