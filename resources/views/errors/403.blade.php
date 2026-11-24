<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Akses Ditolak &middot; KotakRasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-500">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>

            <p class="mt-4 text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Error 403</p>
            <h1 class="mt-1 text-lg font-bold text-slate-900">Akses Ditolak</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Anda tidak memiliki hak akses untuk halaman ini.
            </p>

            @auth
                <a href="{{ route('dashboard') }}"
                   class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700">
                    Kembali ke Beranda
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-400 underline-offset-2 hover:text-slate-600 hover:underline">
                        Keluar dari akun ini
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white">
                    Masuk ke Aplikasi
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
