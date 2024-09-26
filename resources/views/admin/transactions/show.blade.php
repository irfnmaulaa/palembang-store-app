@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <div class="d-flex">
                    <h2 class="mb-0">DETAIL TRANSAKSI</h2>
                </div>
                <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                    <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                    <a href="{{route('admin.histories.index')}}">Riwayat Transaksi</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                    Detail Transaksi
                </div>
            </div>
        </div>

        <x-alert></x-alert>

        <div class="card border shadow-none overflow-hidden">
            <div class="card-body py-4 d-flex flex-column gap-3">
                <div class="row justify-content-between">
                    <div class="col-md-4">
                        <table class="table table-sm" style="font-size: 16px;">
                            <tr>
                                <td style="width: 100px">Tanggal</td>
                                <td style="width: 10px" class="text-center">:</td>
                                <td>{{\Carbon\Carbon::parse($transaction->date)->format('d/m/Y')}}</td>
                            </tr>
                            <tr>
                                <td>No. DO</td>
                                <td class="text-center">:</td>
                                <td>
                                    {{$transaction->code}}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{route('admin.transactions.export_per_transaction', [$transaction])}}" class="btn btn-lg btn-success">
                            <i class="fas fa-file-pdf me-2"></i> Cetak
                        </a>
                    </div>
                </div>
                <div>
                    @php
                        $className = get_table_row_classname($transaction->type);
                    @endphp
                    <table class="table table-responsive table-sm table-bordered table-hover mb-0">
                        <thead>
                        <tr>
                            <th class="text-center" style="width: 150px">Quantity</th>
                            <th>Nama Barang</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width: 150px">Sisa</th>
                            <th class="text-center" style="width: 150px">ID</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transaction->transaction_products as $tp)
                            <tr>
                                <td class="{{ $className }} text-center">
                                    {{$tp->quantity}}
                                    @if($tp->product)
                                    {{$tp->product->unit}}
                                    @endif
                                </td>
                                <td class="{{ $className }}">
                                    @if($tp->product)
                                        <a href="{{route('admin.products.show', $tp->product)}}" class="{{ $className }}">
                                            {{$tp->product->name}} {{$tp->product->variant}}
                                        </a>
                                    @if(!$tp->is_verified)
                                        <span class="badge bg-warning ms-2">Pending</span>
                                    @endif
                                    @endif
                                </td>
                                <td class="{{ $className }}">
                                    {{$tp->note}}
                                </td>
                                <td class="{{ $className }} text-center">
                                    {{$tp->to_stock}}
                                </td>
                                <td class="{{ $className }} text-center">
                                    {{$tp->creator->name}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
