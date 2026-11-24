@extends('layouts.app')

@section('title', 'Pesanan Baru')

@section('content')
    <x-page-header judul="Pesanan Baru" deskripsi="Catat pesanan pelanggan beserta rincian menunya." />

    @php
        $items = old('items', [
            ['menu_id' => '', 'jumlah' => 1, 'harga_satuan' => ''],
        ]);
        $menuHarga = $menus->pluck('harga_standar', 'id');
    @endphp

    <form method="POST" action="{{ route('pesanan.store') }}" class="space-y-4">
        @csrf

        @include('pesanan._form', [
            'items' => $items,
            'menuHarga' => $menuHarga,
            'pesanan' => null,
        ])
    </form>
@endsection
