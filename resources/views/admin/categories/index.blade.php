@extends('layouts.index', [
    'title' => 'Kategori',
    'data' => $categories,
    'order_options' => $order_options,
    'withNoOrder' => true,
])

@section('table')
    <x-alert></x-alert>
    <table class="table table-hover mb-0 table-sm">
        <thead>
        <tr>
            <th>Nama Kategori</th>
            <th style="width: 200px" class="text-center">Jumlah Barang</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $i => $category)
            <tr>
                <td>
                    <a href="{{ route('admin.categories.show', [$category]) }}">{{$category->name}}</a>
                </td>
                <td class="text-center">
                    {{$category->products_count}}
                </td>
            </tr>
        @endforeach
        @if(count($categories) <= 0)
            <tr>
                <td colspan="2" class="text-center">Tidak ada data</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('cta')
    @if(auth()->user()->role === 'admin')
    <div class="d-flex gap-3">
        <x-export-button table="categories"></x-export-button>
        <a href="{{route('admin.categories.create')}}" class="btn btn-primary btn-lg">
            Tambah Kategori
        </a>
    </div>
    @endif
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Kategori
    </div>
@endsection
