@extends('layouts.app')

@section('title', 'Tambah Buku')

@section('content')
    <x-page-header
        title="Tambah Buku"
        description="Lengkapi data buku untuk menambahkannya ke katalog."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Katalog & Buku' => route('books.index'), 'Tambah Buku' => null]"
    />

    <div class="max-w-4xl">
        @include('books.partials.form', [
            'action' => route('books.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Buku',
        ])
    </div>
@endsection
