<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f97316">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>Masuk &middot; KotakRasa</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-kr-cream font-sans text-kr-ink antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm lg:max-w-md">
            {{-- Brand --}}
            <div class="flex flex-col items-center text-center">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-kr-primary text-white shadow-lg shadow-kr-primary/25">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5v9L12 21l9-4.5v-9Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 12l9-4.5M12 12v9" />
                    </svg>
                </span>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-kr-ink">KotakRasa</h1>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-kr-faint">Katering &amp; Invoice</p>
            </div>

            {{-- Kartu login --}}
            <div class="mt-7 rounded-2xl border border-kr-line bg-white p-6 shadow-kr-card">
                <h2 class="text-lg font-bold text-kr-ink">Selamat Datang Kembali</h2>
                <p class="mt-1 text-sm leading-relaxed text-kr-muted">
                    Masuk dengan akun terdaftar. Hak akses otomatis mengikuti role akun Anda.
                </p>

                @if (session('success'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-kr-success-deep">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-kr-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-kr-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-kr-muted">
                            Email
                        </label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="nama@kotakrasa.com"
                               class="w-full rounded-xl border border-kr-line-strong bg-white px-3.5 py-3 text-base text-kr-ink placeholder:text-kr-faint outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25 @error('email') border-rose-400 @enderror">
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-kr-muted">
                            Password
                        </label>
                        <input id="password"
                               name="password"
                               type="password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-kr-line-strong bg-white px-3.5 py-3 text-base text-kr-ink placeholder:text-kr-faint outline-none transition focus:border-kr-primary focus:ring-2 focus:ring-kr-primary/25 @error('password') border-rose-400 @enderror">
                    </div>

                    <button type="submit"
                            class="min-h-12 w-full rounded-[14px] bg-kr-primary px-5 py-3 text-base font-bold text-white shadow-kr-primary/30 transition hover:bg-kr-accent focus:outline-none focus:ring-2 focus:ring-kr-primary focus:ring-offset-2 active:scale-[0.99]">
                        Masuk ke Aplikasi
                    </button>
                </form>

                {{-- Akun demo --}}
                <div class="mt-6 rounded-xl border border-dashed border-kr-line bg-kr-soft/70 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-kr-accent/80">Akun demo (klik untuk mengisi)</p>

                    <button type="button"
                            data-demo-email="ibudika@kotakrasa.com"
                            class="mt-2 flex w-full items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-left text-xs text-kr-brown ring-1 ring-kr-line transition hover:ring-kr-line-strong">
                        <span>
                            <span class="block font-semibold text-kr-ink">Ibu Dika &mdash; Pemilik</span>
                            <span class="text-[11px] text-kr-faint">ibudika@kotakrasa.com</span>
                        </span>
                        <span class="rounded-md bg-kr-soft px-2 py-0.5 text-[10px] font-bold text-kr-accent">Akses penuh</span>
                    </button>

                    <button type="button"
                            data-demo-email="pencatat@kotakrasa.com"
                            class="mt-2 flex w-full items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-left text-xs text-kr-brown ring-1 ring-kr-line transition hover:ring-kr-line-strong">
                        <span>
                            <span class="block font-semibold text-kr-ink">Staf Pencatat &mdash; Pencatat</span>
                            <span class="text-[11px] text-kr-faint">pencatat@kotakrasa.com</span>
                        </span>
                        <span class="rounded-md bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700">Operasional</span>
                    </button>

                    <p class="mt-2 text-[10px] text-kr-faint">Password semua akun demo: <span class="font-semibold text-kr-muted">password123</span></p>
                </div>
            </div>

            <p class="mt-5 text-center text-[11px] text-kr-faint">KotakRasa MVP &middot; Data tersimpan di server</p>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-demo-email]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                document.getElementById('email').value = tombol.dataset.demoEmail;
                document.getElementById('password').value = 'password123';
                document.getElementById('email').focus();
            });
        });
    </script>
</body>
</html>
