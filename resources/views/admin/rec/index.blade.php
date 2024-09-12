@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0 text-uppercase">Redundant Error Checker</h2>
                <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                    <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                    Redundant Error Checker
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="" class="btn btn-primary btn-lg">
                    Cek Redudansi
                </a>
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-body">
            </div>
            <div class="card-body py-0 d-flex flex-column gap-4">
                @foreach($recs as $item)
                    <div class="p-3 border rounded w-100">
                        <h6 class="mb-2">{{$item->product->name}} {{$item->product->variant}} / {{$item->product->code}}</h6>
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 120px;"></th>
                                    <th style="width: 250px;">Tanggal</th>
                                    <th style="width: 250px;">No DO</th>
                                    <th>Keterangan</th>
                                    <th style="width: 100px;" class="text-center">M</th>
                                    <th style="width: 100px;" class="text-center">K</th>
                                    <th style="width: 100px;" class="text-center">S</th>
                                    <th style="width: 100px;" class="text-center">ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>{{\Carbon\Carbon::parse($item->from_transaction_product->transaction->date)->format('d/m/Y')}}</td>
                                    <td>{{$item->from_transaction_product->transaction->code}}</td>
                                    <td>{{$item->from_transaction_product->note}}</td>
                                    <td class="text-center">{{$item->from_transaction_product->transaction->type === 'in' ? $item->from_transaction_product->quantity : 0}}</td>
                                    <td class="text-center">{{$item->from_transaction_product->transaction->type === 'out' ? $item->from_transaction_product->quantity : 0}}</td>
                                    <td class="text-center">{{$item->from_transaction_product->to_stock}}</td>
                                    <td class="text-center">{{@$item->from_transaction_product->creator->name ?? '-'}}</td>
                                </tr>
                                <tr>
                                    <td class="text-center">Error <i class="fas fa-arrow-right ms-1"></i></td>
                                    <td>{{\Carbon\Carbon::parse($item->to_transaction_product->transaction->date)->format('d/m/Y')}}</td>
                                    <td>{{$item->to_transaction_product->transaction->code}}</td>
                                    <td>{{$item->to_transaction_product->note}}</td>
                                    <td class="text-center">{{$item->to_transaction_product->transaction->type === 'in' ? $item->to_transaction_product->quantity : 0}}</td>
                                    <td class="text-center">{{$item->to_transaction_product->transaction->type === 'out' ? $item->to_transaction_product->quantity : 0}}</td>
                                    <td class="text-center">{{$item->to_transaction_product->to_stock}}</td>
                                    <td class="text-center">{{@$item->to_transaction_product->creator->name ?? '-'}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    {{ $recs->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>

        <div class="text-muted"><small>Ditampilkan {{number_format($recs->firstItem(), 0, ',', '.')}} - {{number_format($recs->count() - 1 + $recs->firstItem(), 0, ',', '.')}} dari {{number_format($recs->total(), 0, ',', '.')}} data</small></div>
    </div>
@endsection
