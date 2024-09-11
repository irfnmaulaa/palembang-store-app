@extends('layouts.index', [
    'title' => 'Cek Stok',
    'data' => $categories,
    'order_options' => $order_options,
    'withNoOrder' => true,
])

@section('table')
    <form action="{{route('admin.check_stocks.store')}}" method="POST">
        @csrf
        <table class="table table-hover mb-0 table-sm">
            <thead>
            <tr>
                <th style="width: 50px;">
                    <div class="d-flex justify-content-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="check_all" />
                        </div>
                    </div>
                </th>
                <th>Nama Kategori</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $i => $category)
                <tr>
                    <td>
                        <div class="d-flex justify-content-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{$category->id}}" name="category_ids[]" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.categories.show', [$category]) }}">{{$category->name}}</a>
                    </td>
                </tr>
            @endforeach
            @if(count($categories) <= 0)
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data</td>
                </tr>
            @endif
            </tbody>
        </table>
        <button type="submit" class="btn btn-success btn-lg mt-4">
            <i class="fas fa-print me-2"></i> Cetak
        </button>
    </form>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Cek Stok
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('body').delegate('#check_all', 'change', function() {
                if($(this).is(':checked')) {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', true).trigger('change');
                } else {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', false).trigger('change');
                }
            })

            $('body').delegate('table tbody input[type="checkbox"]', 'change', function() {
                const checkedCount = $(this).parents('table').find('tbody input[type="checkbox"]:checked').length
                if(checkedCount >= $(this).parents('table').find('tbody input[type="checkbox"]').length) {
                    $(this).parents('table').find('#check_all').prop('checked', true)
                } else {
                    $(this).parents('table').find('#check_all').prop('checked', false)
                }

                if(checkedCount > 0) {
                    $('.btn-print').removeClass('disabled')
                } else {
                    $('.btn-print').addClass('disabled')
                }
            })
        })
    </script>
@endsection
