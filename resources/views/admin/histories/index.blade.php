@extends('layouts.index', [
    'title' => 'Riwayat Transaksi',
    'data' => $transactions,
    'order_options' => $order_options,
    'withNoOrder' => true,
])

@section('table')
    <x-verified-transactions-table dir="desc" :transactions="$transactions"></x-verified-transactions-table>
@endsection

@section('cta')
    <div class="d-flex align-items-center gap-3">
        @if(auth()->user()->role === 'admin')
            <x-export-button table="histories" :param="['start_date' => $start ? \Carbon\Carbon::parse($start)->format('Y-m-d') : '', 'end_date' => $end ? \Carbon\Carbon::parse($end)->format('Y-m-d') : '',]"></x-export-button>
        @endif
    </div>
@endsection

@section('top-right')
    <form action="">
        <div class="d-flex align-items-end gap-3">
            <div class="d-flex flex-column gap-1">
                <label for="filter-start-date">Tanggal Mulai</label>
                <input type="date" name="start_date" id="filter-start-date" class="form-control form-control-lg" value="{{$start ? \Carbon\Carbon::parse($start)->format('Y-m-d') : ''}}">
            </div>
            <div class="d-flex flex-column gap-1">
                <label for="filter-start-date">Tanggal Selesai</label>
                <input type="date" name="end_date" id="filter-start-date" class="form-control form-control-lg" value="{{$end ? \Carbon\Carbon::parse($end)->format('Y-m-d') : ''}}">
            </div>
            <button type="submit" class="btn btn-lg btn-primary">Filter</button>
        </div>
    </form>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Riwayat Transaksi
    </div>
@endsection
