# Cara Menjalankan & Membuka KotakRasa di HP

Proyek ini punya dua cara untuk dilihat di HP:

1. **Mode development** (hot reload, untuk ngoding)
2. **Preview build** (satu file `index.html`, untuk dilihat/dibagikan)

---

## 1. Mode development (hot reload)

```bash
cd "Figma Desain"
npm install      # cukup sekali
npm run dev
```

Vite sudah dikonfigurasi `host: "0.0.0.0"`, jadi bisa diakses dari HP.

### Buka dari HP

1. Pastikan **HP dan komputer berada di Wi-Fi yang sama**.
2. Cari IP komputer:
   ```bash
   ipconfig
   ```
   Lihat **IPv4 Address**, contoh `192.168.1.10`.
3. Buka browser HP ke:
   ```
   http://192.168.1.10:5173
   ```
4. Kalau tidak bisa dibuka, izinkan **Node.js** di Windows Firewall untuk jaringan **Private**.

---

## 2. Preview build (file tunggal)

```bash
cd "Figma Desain"
npm run build:preview
```

Perintah itu akan:
- build aplikasi ke `Figma Desain/dist/index.html` (single file, CSS & JS sudah inline), lalu
- menyalin hasilnya ke `index.html` dan `favicon.png` di root proyek.

### Cara membuka

- **Laragon**: buka `http://localhost/Katering/` atau `http://katering.test/`
  (root folder ini sudah bernama `Katering` di `D:\laragon\www`).
- Atau klik dua kali `index.html` (langsung dari file manager).

> Catatan: kalau mengubah file di `Figma Desain/src`, jalankan `npm run build:preview` lagi supaya `index.html` di root ikut ter-update.

---

## 3. Ringkasan perilaku responsif

- **≤1100px**: sidebar lebih ramping, grid utama jadi 1 kolom.
- **≤768px**:
  - sidebar menjadi drawer (tombol menu di topbar),
  - bottom navigation 5 menu,
  - tabel Pesanan, Pengeluaran, dan Laporan berubah menjadi **kartu bertumpuk**,
  - faktur A4 tetap berbentuk tabel (bukan kartu), font & padding dikecilkan,
  - input memakai font 16px agar tidak auto-zoom di iPhone.
- **≤480px**: teks brand & role tag di topbar disembunyikan, kartu metrik 2 kolom.
- Mendukung `safe-area-inset` (iPhone dengan notch) dan `100dvh`.
- Saat pratinjau A4 terbuka, scroll halaman dikunci dan overlay tidak ikut "mentul" (overscroll).
