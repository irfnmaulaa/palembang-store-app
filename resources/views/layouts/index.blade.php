@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0">{{$title}}</h2>
                @yield('breadcrumbs')
            </div>
            @yield('cta')
        </div>

        <div class="card border shadow-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <form action="" method="GET">
                        <input type="hidden" name="page" value="1">
                        @foreach(request()->except(['keyword', 'page']) as $key => $value)
                            <input type="hidden" name="{{$key}}" value="{{$value}}">
                        @endforeach
                        <div class="input-group input-group-lg" style="max-width: 300px;">
                            <input type="text" id="keyword" class="form-control form-control-lg" placeholder="Cari kategori.." name="keyword" value="{{request('keyword')}}"/>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <form action="" method="GET">
                        @foreach(request()->except(['order', 'page']) as $key => $value)
                            <input type="hidden" name="{{$key}}" value="{{$value}}">
                        @endforeach

                        @if(isset($order_options))
                            <div class="d-flex align-items-center gap-2">
                                <span><small class="text-muted">Urutkan</small></span>
                                <div class="input-group input-group-lg">
                                    <select name="order" id="order" class="form-control form-control-lg">
                                        @foreach($order_options as $order_option)
                                            <option value="{{$order_option['order']}}" {{request('order') === $order_option['order'] ? 'selected' : ''}}>
                                                {{ $order_option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body py-0">
                @yield('table')
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="text-muted"><small>Ditampilkan {{number_format($data->firstItem(), 0, ',', '.')}} - {{number_format($data->count() - 1 + $data->firstItem(), 0, ',', '.')}} dari {{number_format($data->total(), 0, ',', '.')}} datagi</small></div>
                    {{ $data->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
