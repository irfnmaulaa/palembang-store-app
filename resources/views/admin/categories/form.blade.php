@extends('layouts.form', [
    'title' => (@$item ? 'Edit' : 'Tambah') . ' Kategori',
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.categories.' . (@$item ? 'update' : 'store'), [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf
            @if(@$item)
                @method('PUT')
            @endif

            <x-textfield label="Nama Kategori" name="name" type="text" :item="$item" autofocus></x-textfield>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                @if(@$item)
                    <a href="{{route('admin.categories.show', [$item])}}" class="btn btn-outline-primary btn-lg">Kembali</a>
                @else
                    <a href="{{route('admin.categories.index')}}" class="btn btn-outline-primary btn-lg">Kembali</a>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.categories.index')}}">Data Kategori</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{@$item ? 'Edit' : 'Tambah'}} Kategori
    </div>
@endsection


