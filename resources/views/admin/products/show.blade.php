@extends('layouts.index', [
    'title' => 'Riwayat Transaksi Barang',
    'data' => $transaction_products,
    'order_options' => $order_options
])

@section('table')
    <table class="table table-bordered mb-0 table-sm">
        <thead>
        <tr>
            <th class="bg-body-tertiary">Tanggal</th>
            <th class="bg-body-tertiary">No DO</th>
            <th class="bg-body-tertiary">Nama Barang</th>
            <th class="bg-body-tertiary">Kode Barang</th>
            <th class="bg-body-tertiary text-center" style="width: 100px">Stok Awal</th>
            <th class="bg-body-tertiary text-center" style="width: 100px">Quantity</th>
            <th class="bg-body-tertiary text-center" style="width: 100px">Stok Akhir</th>
            <th class="bg-body-tertiary">Keterangan</th>
            <th class="bg-body-tertiary">Dibuat oleh</th>
            <th class="bg-body-tertiary">Diverifikasi oleh</th>
        </tr>
        <tbody>
        @foreach($transaction_products as $transaction_product)
            @php
            $className = $transaction_product->type == 'in' ? 'text-primary fw-bold' : 'text-danger fw-bold';
            @endphp
            <tr>
                <td class="{{$className}}">{{\Carbon\Carbon::parse($transaction_product->date)->format('d/m/Y H:i')}}</td>
                <td class="{{$className}}">{{$transaction_product->code}}</td>
                <td class="{{$className}}">{{$transaction_product->product->name}}</td>
                <td class="{{$className}}">{{$transaction_product->product->code}}</td>
                <td class="{{$className}} text-center">{{$transaction_product->from_stock}}</td>
                <td class="{{$className}} text-center">{{$transaction_product->quantity}}</td>
                <td class="{{$className}} text-center">{{$transaction_product->to_stock}}</td>
                <td class="{{$className}}">{{$transaction_product->note}}</td>
                <td class="{{$className}}">{{$transaction_product->creator->name}}</td>
                <td class="{{$className}}">{{$transaction_product->verificator->name}}</td>
            </tr>
        @endforeach
        @if(count($transaction_products) == 0)
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('cta')
    <div class="d-flex align-items-center gap-3">

    </div>
@endsection

@section('top-right')
    <form action="">
        <div class="input-group input-group-lg">
            <input type="text" name="date_range" class="form-control form-control-lg date-range-picker" style="min-width: 220px">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Riwayat Transaksi
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
