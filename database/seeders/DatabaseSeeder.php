<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
use App\Models\Pelanggan;
use App\Models\KategoriPengeluaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna Sesuai PRD (Pemilik & Pencatat)
        $pemilik = User::updateOrCreate(
            ['email' => 'ibudika@kotakrasa.com'],
            [
                'name' => 'Ibu Dika',
                'password' => Hash::make('password123'),
                'role' => 'pemilik',
            ]
        );

        $pencatat = User::updateOrCreate(
            ['email' => 'pencatat@kotakrasa.com'],
            [
                'name' => 'Staf Pencatat',
                'password' => Hash::make('password123'),
                'role' => 'pencatat',
            ]
        );

        // 2. Kategori Pengeluaran Utama Sesuai PRD (F3)
        $kategoriList = [
            ['nama_kategori' => 'Bahan', 'deskripsi' => 'Belanja bahan baku makanan, bumbu, beras, ayam/daging'],
            ['nama_kategori' => 'Kemasan', 'deskripsi' => 'Kotak kardus, mika snack, kantong plastik, sendok garpu'],
            ['nama_kategori' => 'Gas/Listrik', 'deskripsi' => 'Tabung gas elpiji dan biaya listrik dapur'],
            ['nama_kategori' => 'Ongkos antar', 'deskripsi' => 'Bensin atau sewa kurir pengantaran pesanan'],
            ['nama_kategori' => 'Gaji/Bantuan', 'deskripsi' => 'Upah tenaga pembantu dapur saat acara besar'],
            ['nama_kategori' => 'Lainnya', 'deskripsi' => 'Biaya tak terduga dan perlengkapan kebersihan'],
        ];

        foreach ($kategoriList as $kat) {
            KategoriPengeluaran::updateOrCreate(
                ['nama_kategori' => $kat['nama_kategori']],
                ['deskripsi' => $kat['deskripsi']]
            );
        }

        // 3. Menu Contoh Usaha Katering Sesuai PRD (Box dan Snack)
        $menuList = [
            ['nama_menu' => 'Nasi Box Ayam Bakar Madu', 'jenis' => 'box', 'harga_standar' => 25000],
            ['nama_menu' => 'Nasi Box Ayam Goreng Lengkuas', 'jenis' => 'box', 'harga_standar' => 23000],
            ['nama_menu' => 'Nasi Tumpeng Mini Komplit', 'jenis' => 'box', 'harga_standar' => 30000],
            ['nama_menu' => 'Snack Box Lemper & Pastel', 'jenis' => 'snack', 'harga_standar' => 12000],
            ['nama_menu' => 'Snack Box Risoles Mayo & Lumpur', 'jenis' => 'snack', 'harga_standar' => 13000],
            ['nama_menu' => 'Botok Ayam Telur Asin', 'jenis' => 'box', 'harga_standar' => 15000],
        ];

        foreach ($menuList as $m) {
            Menu::updateOrCreate(
                ['nama_menu' => $m['nama_menu']],
                [
                    'jenis' => $m['jenis'],
                    'harga_standar' => $m['harga_standar'],
                ]
            );
        }

        // 4. Pelanggan Dummy Awal
        Pelanggan::updateOrCreate(
            ['nama' => 'Ibu Rina (Banjarsari)'],
            [
                'no_telepon_wa' => '081234567890',
                'catatan' => 'Langganan arisan RT 03',
            ]
        );

        Pelanggan::updateOrCreate(
            ['nama' => 'Pak Bambang (Kelurahan)'],
            [
                'no_telepon_wa' => '081298765432',
                'catatan' => 'Sering pesan snack box rapat kantor',
            ]
        );
    }
}
