@extends('layouts.app')

@section('title', 'Ubah Pengeluaran')

@section('content')
    <x-page-header judul="Ubah Pengeluaran"
                   deskripsi="Perbaiki data pengeluaran yang sudah tercatat." />

    <form method="POST" action="{{ route('pengeluaran.update', $pengeluaran) }}" class="space-y-4">
        @csrf
        @method('PUT')

        @include('pengeluaran._form')
    </form>
@endsection
