@extends('layouts.index', [
    'title' => 'Buku Besar',
    'data' => $transaction_products,
    'order_options' => $order_options,
    'withNoOrder' => true,
])

@section('header')
    <x-alert></x-alert>
    <div class="card shadow-none border">
        <div class="card-body">
            <div class="mb-3 d-flex align-items-center justify-content-between">
                <h3 class="text-uppercase mb-0">{{$product->name}}</h3>
                @if(auth()->user()->role === 'admin')
                <div class="d-flex gap-3">
                    <a href="{{route('admin.products.edit', [$product])}}" class="btn btn-lg btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Edit Barang
                    </a>
                    <a href="{{route('admin.products.destroy', [$product])}}" class="btn btn-delete with-pin btn-lg btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Hapus Barang
                    </a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-6 col-12">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <tr>
                            <td style="width: 160px;">Variant</td>
                            <td style="width: 10px" class="text-center">:</td>
                            <td>{{$product->variant}}</td>
                        </tr>
                        <tr>
                            <td>Kode Barang</td>
                            <td>:</td>
                            <td>{{$product->code}}</td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>:</td>
                            <td>
                                @if($product->category)
                                    <a href="{{route('admin.categories.show', [$product->category])}}">{{$product->category->name}}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Stok saat ini</td>
                            <td>:</td>
                            <td>{{$product->stock ?? 0}} {{$product->unit}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('table')
    <table class="table table-hover mb-0 table-sm">
        <thead>
        <tr>
            <th class="bg-body-tertiary" style="width: 180px">Tanggal</th>
            <th class="bg-body-tertiary" style="width: 150px">No DO</th>
            <th class="bg-body-tertiary">Keterangan</th>
            <th class="bg-body-tertiary text-center" style="width: 60px">M</th>
            <th class="bg-body-tertiary text-center" style="width: 60px">K</th>
            <th class="bg-body-tertiary text-center" style="width: 60px">S</th>
            <th class="bg-body-tertiary text-center" style="width: 120px">ID</th>
        </tr>
        <tbody>
        @foreach($transaction_products as $transaction_product)
            @php
                $type = $transaction_product->type;
                $className = get_table_row_classname($type);
            @endphp
            <tr>
                <td class="{{$className}}">{{\Carbon\Carbon::parse($transaction_product->date)->format('d/m/Y')}}</td>
                <td class="{{$className}}">
                    <a href="{{ route('admin.transactions.show', [$transaction_product]) }}" class="{{$className}}">
                        {{$transaction_product->code}}
                    </a>
                </td>
                <td class="{{$className}}">{{$transaction_product->note}}</td>
                <td class="{{$className}} text-center">{{$type === 'in' ? $transaction_product->quantity : '0'}}</td>
                <td class="{{$className}} text-center">{{$type === 'out' ? $transaction_product->quantity : '0'}}</td>
                <td class="{{$className}} text-center">{{$transaction_product->to_stock}}</td>
                <td class="{{$className}} text-center">
                    @if($transaction_product->transaction->creator)
                        {{ $transaction_product->transaction->creator->name }}
                    @endif
                </td>
            </tr>
        @endforeach
        @if(count($transaction_products) == 0)
            <tr>
                <td colspan="9" class="text-center">Tidak ada data</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('cta')
    @if(auth()->user()->role === 'admin')
    <div class="d-flex align-items-center gap-3">
        <x-export-button table="product-detail" :param="['product'=>$product->id, 'date_range' => request()->query('date_range') ?? '']"></x-export-button>
    </div>
    @endif
@endsection

@section('top-right')
    <form action="">
        <div class="input-group input-group-lg">
            <input type="text" name="date_range" class="form-control form-control-lg date-range-picker" style="min-width: 260px">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.products.index')}}">Data Barang</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Buku Besar
    </div>
@endsection

@section('js')
    <script>
        const start = moment('{{$start}}');
        const end = moment('{{$end}}');

        $('.date-range-picker').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 hari terakhir': [moment().subtract(6, 'days'), moment()],
                '30 hari terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    </script>
@endsection
