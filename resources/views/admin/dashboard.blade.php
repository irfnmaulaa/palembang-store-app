@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-2">
        <div class="alert alert-success">
            Selamat datang, <b>{{auth()->user()->name}}.</b>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card border border-black shadow-none">
                    <div class="card-body text-center">
                        <p class="mb-2">Total Barang</p>
                        <h2 class="mb-0">{{number_format($products_count, 0, ',', '.')}}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border border-black shadow-none">
                    <div class="card-body text-center">
                        <p class="mb-2">Total Kategori</p>
                        <h2 class="mb-0">{{number_format($categories_count, 0, ',', '.')}}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border border-black shadow-none">
                    <div class="card-body text-center">
                        <p class="mb-2">Total Transaksi</p>
                        <h2 class="mb-0">{{number_format($transactions_count, 0, ',', '.')}}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
