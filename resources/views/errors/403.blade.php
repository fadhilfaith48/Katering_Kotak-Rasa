<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Akses Ditolak &middot; KotakRasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-kr-cream font-sans text-kr-ink antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm rounded-2xl border border-kr-line bg-white p-6 text-center shadow-kr-card">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-kr-danger">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>

            <p class="mt-4 text-xs font-bold uppercase tracking-[0.18em] text-kr-danger">Error 403</p>
            <h1 class="mt-1 text-lg font-bold text-kr-ink">Akses Ditolak</h1>
            <p class="mt-2 text-sm leading-relaxed text-kr-muted">
                Anda tidak memiliki hak akses untuk halaman ini.
            </p>

            @auth
                <a href="{{ route('dashboard') }}"
                   class="mt-5 inline-flex min-h-12 items-center gap-1.5 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-primary/25 transition hover:bg-kr-accent">
                    Kembali ke Beranda
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-kr-faint underline-offset-2 hover:text-kr-muted hover:underline">
                        Keluar dari akun ini
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="mt-5 inline-flex min-h-12 items-center gap-1.5 rounded-[14px] bg-kr-primary px-4 py-3 text-base font-bold text-white shadow-kr-primary/25 transition hover:bg-kr-accent">
                    Masuk ke Aplikasi
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
