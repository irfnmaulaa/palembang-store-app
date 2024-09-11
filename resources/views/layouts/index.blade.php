@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0 text-uppercase">{{$title}}</h2>
                @yield('breadcrumbs')
            </div>
            @yield('cta')
        </div>

        @yield('header')

        <div class="card border shadow-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        @yield('top-left')
                        @if(!@$withNoSearch)
                        <form action="" method="GET">
                            <input type="hidden" name="page" value="1">
                            @foreach(request()->except(['keyword', 'page']) as $key => $value)
                                <input type="hidden" name="{{$key}}" value="{{$value}}">
                            @endforeach
                            <div class="input-group input-group-lg" style="max-width: 300px;">
                                <input type="text" id="keyword" class="form-control form-control-lg" placeholder="Cari.." name="keyword" value="{{request('keyword')}}"/>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @if(!@$withNoOrder)
                        <form action="" method="GET">
                            @if(isset($order_options))
                                @foreach(request()->except(['order', 'page']) as $key => $value)
                                    <input type="hidden" name="{{$key}}" value="{{$value}}">
                                @endforeach
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
                        @endif

                        @if(!@$withNoFilter)
                            @yield('filter')
                        @endif
                        @yield('top-right')
                    </div>
                </div>
            </div>
            <div class="card-body py-0">
                @yield('table')
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    {{ $data->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>

        <div class="text-muted"><small>Ditampilkan {{number_format($data->firstItem(), 0, ',', '.')}} - {{number_format($data->count() - 1 + $data->firstItem(), 0, ',', '.')}} dari {{number_format($data->total(), 0, ',', '.')}} data</small></div>
    </div>
@endsection
