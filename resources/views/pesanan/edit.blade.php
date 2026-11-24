@extends('layouts.app')

@section('title', 'Ubah Pesanan')

@section('content')
    <x-page-header
        :judul="'Ubah '.$pesanan->nomor"
        deskripsi="Perbarui data pesanan atau rincian itemnya." />

    @php
        $items = old('items', $pesanan->items->map(fn ($item) => [
            'menu_id' => $item->menu_id,
            'jumlah' => $item->jumlah,
            'harga_satuan' => (float) $item->harga_satuan,
        ])->all());

        $menuHarga = $menus->pluck('harga_standar', 'id');
    @endphp

    <form method="POST" action="{{ route('pesanan.update', $pesanan) }}" class="space-y-4">
        @csrf
        @method('PUT')

        @include('pesanan._form', [
            'items' => $items,
            'menuHarga' => $menuHarga,
            'pesanan' => $pesanan,
        ])
    </form>
@endsection
