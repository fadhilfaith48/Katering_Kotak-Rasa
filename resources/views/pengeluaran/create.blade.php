@extends('layouts.app')

@section('title', 'Pengeluaran Baru')

@section('content')
    <x-page-header judul="Pengeluaran Baru"
                   deskripsi="Catat uang keluar untuk belanja dan kebutuhan dapur." />

    <form method="POST" action="{{ route('pengeluaran.store') }}" class="space-y-4">
        @csrf

        @include('pengeluaran._form')
    </form>
@endsection
