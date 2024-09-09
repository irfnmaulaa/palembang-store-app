@extends('layouts.form', [
    'title' => (@$item ? 'Edit' : 'Tambah') . ' Barang',
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.products.' . (@$item ? 'update' : 'store'), [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf
            @if(@$item)
                @method('PUT')
            @endif
            <div class="form-group">
                <label for="product_category_id">Pilih Kategori</label>
                <select name="product_category_id" id="product_category_id" class="form-control select-category">
                    @if(@$item)
                        <option value="{{$item->product_category_id}}" selected="selected">
                            {{$item->category->name}}
                        </option>
                    @endif
                </select>
                @if($errors->first('product_category_id'))
                    <small class="text-danger mb-0">{{$errors->first('product_category_id')}}</small>
                @endif
            </div>

            <x-textfield label="Nama Barang" name="name" type="text" :item="$item"></x-textfield>

            <x-textfield label="Varian" name="variant" type="text" :item="$item"></x-textfield>

            <x-textfield label="Kode" name="code" type="text" :item="$item"></x-textfield>

            <x-textfield label="Satuan" name="unit" type="text" :item="$item"></x-textfield>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                <a href="{{route('admin.products.index')}}" class="btn btn-outline-primary btn-lg">Batalkan</a>
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.products.index')}}">Data Barang</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{@$item ? 'Edit' : 'Tambah'}} Barang
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $( '.select-category' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                ajax: {
                    delay: 250,
                    url: '{{route('admin.categories.index')}}',
                    data: function (params) {
                        var query = {
                            keyword: params.term,
                            page: params.page || 1
                        }
                        // Query parameters will be ?search=[term]&page=[page]
                        return query;
                    }
                },
                tags: true,
                createTag: function(params) {
                    return { id: `new_${params.term}`, text: `Tambah kategori baru "${params.term}"` };
                }
            } );
        })
    </script>
@endsection
