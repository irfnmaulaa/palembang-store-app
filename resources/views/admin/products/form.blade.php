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

            <x-textfield label="Nama Barang" name="name" type="text" :item="$item" autofocus></x-textfield>

            <x-textfield label="Varian" name="variant" type="text" :item="$item"></x-textfield>

            <x-textfield label="Kode" name="code" type="text" :item="$item"></x-textfield>

            <x-textfield label="Satuan" name="unit" type="text" :item="$item"></x-textfield>

            <div class="form-group" id="product-category-wrap" style="position:relative;">
                <label for="product_category_id">Pilih Kategori</label>
                <select name="product_category_id" id="product_category_id" class="form-control select-category">
                    @if(@$item && $item->category)
                        <option value="{{$item->product_category_id}}" selected="selected">
                            {{$item->category->name}}
                        </option>
                    @endif
                </select>
                @if($errors->first('product_category_id'))
                    <small class="text-danger mb-0">{{$errors->first('product_category_id')}}</small>
                @endif
            </div>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Simpan</button>

                @if(@$item)
                    <a href="{{route('admin.products.show', [$item])}}" class="btn btn-outline-primary btn-lg">Kembali</a>
                @else
                    <a href="{{route('admin.products.index')}}" class="btn btn-outline-primary btn-lg">Kembali</a>
                @endif
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
                },
                dropdownParent: $('.select-category').parents('.form-group')
            } );
        })

        $('input,textarea,select').on('keydown', (e) => {
            if(e.key === 'Enter') {
                e.preventDefault()
                const nextInput = $(e.target).parents('.form-group').next('.form-group').find('input,textarea,select')
                if(nextInput.hasClass('select-category')) {
                    $('.select-category').select2('open')
                    $('.select-category').on('select2:close', function () {
                        console.log($('[type="submit"]'))
                        $('[type="submit"]').focus()
                    })
                } else {
                    nextInput.focus()
                }
            }
        })
    </script>

    @if(@$item)
        <script>
            $('form').submit(function (e) {
                if($(this).find('[name="pin"]').length <= 0) {
                    e.preventDefault()

                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: `btn btn-primary btn-lg me-3`,
                            cancelButton: `btn btn-outline-primary btn-lg`
                        },
                        buttonsStyling: false
                    });
                    swalWithBootstrapButtons.fire({
                        title: "Konfirmasi Pembaruan",
                        text: 'Masukan PIN untuk dapat memperbarui detail barang',
                        input: "password",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: 'Perbarui Barang',
                        cancelButtonText: "Batalkan",
                        showLoaderOnConfirm: true,
                        preConfirm: async (pin) => new Promise((resolve, reject) => {
                            $.ajax({
                                url: '{{route('admin.users.check_pin')}}',
                                method: 'POST',
                                data: {
                                    _token: '{{csrf_token()}}',
                                    pin,
                                },
                                success: () => {
                                    const pinInput = $(`<input type="hidden" name="pin" value="${ pin }"/>`)
                                    $('form').prepend(pinInput)
                                    resolve()
                                },
                                error: ({responseJSON}) => {
                                    Swal.showValidationMessage(responseJSON.message || `Pin tidak valid`);
                                    resolve()
                                },
                            })
                        }),
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if(result.isConfirmed) {
                            $('form').submit()
                        }
                    });
                }
            })
        </script>
    @endif
@endsection
