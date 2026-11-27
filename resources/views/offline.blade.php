<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f97316">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>Koneksi Terputus &middot; KotakRasa</title>

    {{-- Halaman ini sengaja mandiri supaya tetap tampil walau aset build belum termuat. --}}
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #FBF7F2;
            color: #1F1B17;
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
        main {
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        .logo {
            width: 64px;
            height: 64px;
            margin: 0 auto;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: #F97316;
            box-shadow: 0 10px 24px rgba(249, 115, 22, .25);
        }
        h1 { margin: 20px 0 8px; font-size: 22px; letter-spacing: -.02em; }
        p { margin: 0; color: #806B5F; font-size: 14px; line-height: 1.6; }
        .aksi { margin-top: 24px; display: grid; gap: 10px; }
        button, a {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        button {
            border: 0;
            background: #F97316;
            color: #fff;
            padding: 0 20px;
        }
        a {
            border: 1px solid #E8DED6;
            background: #fff;
            color: #584237;
            padding: 0 20px;
        }
        small { display: block; margin-top: 20px; color: #705C50; font-size: 11px; }
    </style>
</head>
<body>
    <main>
        <div class="logo" aria-hidden="true">
            <svg class="h-8 w-8" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5v9L12 21l9-4.5v-9Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 12l9-4.5M12 12v9" />
            </svg>
        </div>

        <h1>Tidak ada koneksi</h1>
        <p>Data KotakRasa tersimpan di server. Sambungkan kembali ke internet, lalu coba lagi.</p>

        <div class="aksi">
            <button type="button" onclick="location.reload()">Coba lagi</button>
            <a href="/login">Ke halaman masuk</a>
        </div>

        <small>Aplikasi tetap terpasang di perangkat Anda.</small>
    </main>
</body>
</html>
