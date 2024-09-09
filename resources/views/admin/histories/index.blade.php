@extends('layouts.index', [
    'title' => 'Riwayat Transaksi',
    'data' => $transactions,
    'order_options' => $order_options
])

@section('table')
    <table class="table table-bordered mb-0 table-sm">
        <thead>
        <tr>
            <th class="bg-body-tertiary" rowspan="2">Tanggal</th>
            <th class="bg-body-tertiary" rowspan="2">No DO</th>
            <th class="bg-body-tertiary" rowspan="2">Tipe</th>
            <th rowspan="2" class="bg-body-tertiary">Dibuat oleh</th>
            <th colspan="4" class="bg-body-tertiary text-center">Barang</th>
        </tr>
        <tr>
            <th class="bg-body-tertiary">Nama Barang</th>
            <th class="bg-body-tertiary">Kode Barang</th>
            <th class="bg-body-tertiary text-center" style="width: 100px">Quantity</th>
            <th class="bg-body-tertiary">Diverifikasi oleh</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $i => $transaction)
            @php
                $count = $transaction->transaction_products()->where('is_verified', 1)->count() + 1;
                $products = $transaction->products()->wherePivot('is_verified', 1)->get();
            @endphp
            <tr>
                <td rowspan="{{$count}}">
                    <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                        {{\Carbon\Carbon::parse($transaction->date)->format('d/m/Y')}}
                    </label>
                </td>
                <td rowspan="{{$count}}">
                    <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                        {{$transaction->code}}
                    </label>
                </td>
                <td rowspan="{{$count}}" class="{{$transaction->type === 'out' ? 'text-danger' : 'text-info'}}">
                    <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                        Barang {{$transaction->type === 'in' ? 'Masuk' : 'Keluar'}}
                    </label>
                </td>
                <td rowspan="{{$count}}">
                    <label for="tp-{{$transaction->id}}" class="d-flex align-items-center">
                        @if($transaction->creator)
                            {{$transaction->creator->name}}
                        @else
                            -
                        @endif
                    </label>
                </td>
            </tr>
            @foreach($products as $product)
                <tr>
                    <td>
                        {{$product->name}}
                    </td>
                    <td>
                        {{$product->code}}
                    </td>
                    <td class="text-center">
                        {{$product->pivot->quantity}}
                    </td>
                    <td>
                        @if($product->pivot->verified_by)
                            @php
                            $verificator = \App\Models\User::find($product->pivot->verified_by);
                            @endphp
                            {{$verificator->name}}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        @if(count($transactions) == 0)
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
