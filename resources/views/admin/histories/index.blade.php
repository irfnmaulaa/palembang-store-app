@extends('layouts.index', [
    'title' => 'Riwayat Transaksi',
    'data' => $transactions,
    'order_options' => $order_options
])

@section('table')
    <x-verified-transactions-table :transactions="$transactions"></x-verified-transactions-table>
@endsection

@section('cta')
    <div class="d-flex align-items-center gap-3">
        @if(auth()->user()->role === 'admin')
            <x-export-button table="histories" :param="['date_range' => request()->query('date_range') ?? '']"></x-export-button>
        @endif
    </div>
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
