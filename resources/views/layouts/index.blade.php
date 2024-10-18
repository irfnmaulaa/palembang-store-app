@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0 text-uppercase">{{$title}}</h2>
                @yield('breadcrumbs')
            </div>
            <div class="cta-wrap">
                @yield('cta')
            </div>
        </div>

        @yield('header')

        <div class="card border shadow-none">
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        @yield('top-left')
                        @if(!@$withNoSearch)
                        <form action="" method="GET">
                            <input type="hidden" name="page" value="1">
                            @foreach(request()->except(['keyword', 'page']) as $key => $value)
                                <input type="hidden" name="{{$key}}" value="{{$value}}">
                            @endforeach

                            @if(!@$advanceSearching)
                            <label for="search-box">Pencarian</label>
                            <div class="input-group input-group-lg" style="max-width: 300px;">
                                <input type="text" id="keyword" class="form-control form-control-lg" name="keyword" value="{{request('keyword')}}"/>
                                <button class="btn btn-primary" type="submit">
                                    Cari
                                </button>
                            </div>
                            @else
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="keyword" class="form-control form-control-lg" name="keyword" value="{{request('keyword')}}" style="width: 230px;"/>
                                <label class="form-label" for="search-box">Pencarian</label>
                            </div>
                            @endif
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
            <div id="table-wrap" class="card-body py-0 position-relative">
                @yield('table')
            </div>
            <div class="card-body">
                @if(!@$withNoPagination)
                <div id="pagination-wrap" class="d-flex justify-content-center align-items-center gap-2">
                    {{ $data->links('vendor.pagination.bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>

        @if(!@$withNoSummary)
        <div id="summary-wrap">
            <div class="text-muted"><small>Ditampilkan {{number_format($data->firstItem(), 0, ',', '.')}} - {{number_format($data->count() - 1 + $data->firstItem(), 0, ',', '.')}} dari {{number_format($data->total(), 0, ',', '.')}} data</small></div>
        </div>
        @endif
    </div>
@endsection

@section('advance_js')
    @if(@$advanceSearching && @$searchingUrl)
        <script>
            $(document).ready(function () {
                let searchTimeout = null
                const tableLoading = $('<div class="loading-container"><div class="loading-bar"></div></div>')
                $('input[name="keyword"]').on('input', function () {
                    if(searchTimeout) {
                        clearTimeout(searchTimeout)
                    }
                    if(!$('#table-wrap').data('is-loading')) {
                        $('#table-wrap').css({
                            opacity: '0.5',
                        }).append(tableLoading).data('is-loading', true)
                    }
                    searchTimeout = setTimeout(() => {
                        const keyword = $(this).val()
                        $.ajax({
                            method: 'GET',
                            url: `{{ $searchingUrl }}?response=view&keyword=${ keyword }`,
                            success: function (response) {
                                $('#table-wrap').html(response.table)
                                $('#pagination-wrap').html(response.pagination)
                                $('#summary-wrap').html(response.summary)
                            },
                            complete: function() {
                                $('#table-wrap').css({
                                    opacity: '1',
                                }).remove(tableLoading).removeData('is-loading')
                            }
                        })
                    }, 200)
                })
            })
        </script>
    @endif
@endsection
