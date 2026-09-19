@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.categories._form')

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
