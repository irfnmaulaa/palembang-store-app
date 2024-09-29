@extends('layouts.form', [
    'title' => $action
])

@section('form')
    <div>
        <x-alert></x-alert>

        <div class="row">
            <div class="col-lg-4 col-xl-3">
              <div class="card shadow-none border">
                  <div class="card-body">
                      <h3 class="card-title mb-3">Input {{$action}}</h3>

                      <form action="" class="d-flex flex-column gap-3" id="form-add-product">
                          <div class="form-group" id="select-product-wrap" style="position:relative;">
                              <label for="product">Pilih Barang</label>
                              <select name="product" id="product" class="form-control select-product">
                              </select>
                          </div>

                          @if(request()->query('type') === 'in')
                              <x-textfield label="Kode Barang" name="product_code" type="text"></x-textfield>
                          @endif

                          <x-textfield label="Quantity" name="quantity" type="number"></x-textfield>

                          <x-textarea label="Keterangan" name="note"></x-textarea>

                          <div class="form-group mt-2 d-flex gap-3">
                              <button type="submit" class="btn btn-outline-primary btn-lg w-100 btn-input">Input</button>
                          </div>
                      </form>
                  </div>
              </div>
            </div>
            <div class="col-lg-8 col-xl-9">
                <div class="card shadow-none border h-100">
                    <div class="card-body d-flex flex-column gap-4 justify-content-between">
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex align-items-end justify-content-between">
                                <h3 class="card-title mb-0">Daftar {{$action}}</h3>
                                <div class="d-flex gap-3">
                                    @if(auth()->user()->role === 'admin')
                                        <x-textfield label="Tanggal" name="date" type="date" :item="$item"></x-textfield>
                                    @endif
                                    <x-textfield label="No DO" name="code" type="text" :item="$item"></x-textfield>
                                </div>
                            </div>
                            <table class="table-bordered table-hover table mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 150px;" class="text-center bg-body-tertiary">Quantity</th>
                                    <th class="bg-body-tertiary">Nama Barang</th>
                                    <th class="bg-body-tertiary">Keterangan</th>
                                    <th style="width: 150px;" class="bg-body-tertiary text-center">Sisa</th>
                                    <th style="width: 80px;" class="text-center bg-body-tertiary">Aksi</th>
                                </tr>
                                </thead>
                                <tbody id="table-body-products">
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <a href="" class="btn btn-outline-danger btn-reset btn-lg">Kosongkan Tabel</a>
                            <div class="d-flex gap-3">
                                <a href="" class="btn btn-outline-success btn-save with-print btn-print btn-lg"><i class="fas fa-print me-2"></i> Simpan dan Cetak</a>
                                <a href="" class="btn btn-primary btn-lg btn-save">Simpan {{$action}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.transactions.index')}}">Data Transaksi</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{@$item ? 'Edit' : 'Tambah'}} {{$action}}
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            const codeEl = $('input[name="code"]')
            const dateEl = $('input[name="date"]')

            $( '.select-product' ).select2( {
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                ajax: {
                    delay: 250,
                    url: '{{route('admin.products.index')}}',
                    data: function (params) {
                        var query = {
                            keyword: params.term,
                            page: params.page || 1
                        }
                        if(dateEl.length > 0) {
                            query.date = dateEl.val()
                        }
                        return query;
                    }
                },
                dropdownParent: $('.select-product').parents('.form-group')
            } );

            $('.select-product').select2('open')

            $('.select-product').on('select2:close', function () {
                const productCode = JSON.parse($('.select-product').val())?.code || ''
                $('[name="product_code"]').val(productCode)

                const nextInput = $(this).parents('.form-group').next('.form-group').find('input')
                nextInput.focus()
            })

            $('input,textarea,select').on('keydown', (e) => {
                if(e.key === 'Enter') {
                    e.preventDefault()
                    const nextInput = $(e.target).parents('.form-group').next('.form-group').find('input,textarea,select,[type="submit"]')
                    nextInput.focus()
                }
            })

            let products = []
            if(localStorage.getItem('_products_{{request()->query('type')}}')) {
                products = JSON.parse(localStorage.getItem('_products_{{request()->query('type')}}'))
            }

            $('#form-add-product').submit(function (e) {
                e.preventDefault()

                const product = $(this).serializeArray()

                if(getValue(product, 'product') === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Pilih barang terlebih dahulu",
                    }).then(() => {
                        setTimeout(() => {
                            $('.select-product').select2('open')
                        }, 300)
                    })
                    return
                }

                if(getValue(product, 'quantity') === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Quantity harus diisi",
                    }).then(() => {
                        setTimeout(() => {
                            $('input[name="quantity"]').focus()
                        }, 300)
                    })
                    return
                }

                if(parseInt(getValue(product, 'quantity'), 0) <= 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Quantity minimal 1",
                    }).then(() => {
                        setTimeout(() => {
                            $('textarea[name="quantity"]').focus()
                        }, 300)
                    })
                    return
                }

                if(getValue(product, 'note') === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Keterangan harus diisi",
                    }).then(() => {
                        setTimeout(() => {
                            $('textarea[name="note"]').focus()
                        }, 300)
                    })
                    return
                }

                // check remaining
                let to_stock = parseInt(JSON.parse(getValue(product, 'product'))?.stock || 0, 10) {{ request()->query('type') == 'in' ? '+' : '-' }} parseInt(getValue(product, 'quantity'), 10);
                to_stock {{ request()->query('type') == 'in' ? '+' : '-' }}= products.filter(prod => {
                    return JSON.parse(getValue(product, 'product'))?.id === JSON.parse(getValue(prod, 'product'))?.id
                }).reduce((carry, prod) => {
                    carry += parseInt(getValue(prod, 'quantity'), 10)
                    return carry
                }, 0)

                if(to_stock < 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Quantity melebihi jumlah stock barang",
                    });
                    return
                }
                products.push(product)
                renderTableProducts()

                $(this)[0].reset()
                $( '.select-product' ).val(null).trigger('change').select2('open')

            })
            renderTableProducts()

            function renderTableProducts() {
                const tbody = $('#table-body-products')
                tbody.html('')

                localStorage.setItem('_products_{{request()->query('type')}}', JSON.stringify(products))

                if(products.length > 0) {
                    products.forEach((product, i) => {

                        // calculate stock
                        let to_stock = parseInt(JSON.parse(getValue(product, 'product'))?.stock || 0, 10) {{ request()->query('type') == 'in' ? '+' : '-' }} parseInt(getValue(product, 'quantity'), 10);
                        to_stock {{ request()->query('type') == 'in' ? '+' : '-' }}= products.filter((prod, j) => {
                            return JSON.parse(getValue(product, 'product'))?.id === JSON.parse(getValue(prod, 'product'))?.id && j < i
                        }).reduce((carry, prod) => {
                            carry += parseInt(getValue(prod, 'quantity'), 10)
                            return carry
                        }, 0)

                        const className = '{{get_table_row_classname(request()->query('type'))}}';
                        const trInit = `
                        <tr>
                            <td class="${className} text-center">
                                ${ getValue(product, 'quantity') } ${ JSON.parse(getValue(product, 'product'))?.unit || '' }
                            </td>
                            <td class="${className}">
                                ${ JSON.parse(getValue(product, 'product'))?.name || '-' }
                            </td>
                            <td class="${className}">
                                ${ getValue(product, 'note') }
                            </td>
                            <td class="text-center ${className}">
                                ${ to_stock }
                            </td>
                            <td class="text-center">
                                <a href="#" class="text-danger btn-remove-product" data-index=${ i } title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    `
                        const tr = $(trInit)
                        tbody.append(tr)
                    })
                } else {
                    tbody.html(`<tr><td class="text-center" colspan="7">Tidak ada data</td></tr>`)
                }
            }

            function getValue(values, field) {
                return values.find(value => value.name === field)?.value || ''
            }

            $('body').delegate('.btn-remove-product', 'click', function (e) {
                e.preventDefault()
                const index = $(this).data('index')
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-danger btn-lg me-3",
                        cancelButton: "btn btn-outline-danger btn-lg"
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "Apakah kamu yakin ingin menghapus?",
                    text: "Data yang telah dihapus tidak bisa dikembalikan.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Tidak",
                }).then((result) => {
                    if ( result.isConfirmed ) {
                        products = products.filter((product, i) => {
                            return i + '' !== index + ''
                        })
                        renderTableProducts()
                    }
                });
            })

            $('.btn-reset').click(function (e) {
                e.preventDefault()
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-danger btn-lg me-3",
                        cancelButton: "btn btn-outline-danger btn-lg"
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "Apakah kamu yakin ingin mengosongkan tabel?",
                    text: "Data yang telah dihapus tidak bisa dikembalikan.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, kosongkan",
                    cancelButtonText: "Tidak",
                }).then((result) => {
                    if ( result.isConfirmed ) {
                        products = []
                        renderTableProducts()
                    }
                });
            })

            codeEl.change(function() {
                localStorage.setItem('_transaction_code_{{request()->query('type')}}', codeEl.val())
            })

            dateEl.change(function() {
                const product_ids = products.map(prod => JSON.parse(prod.find(({name}) => name ==='product').value).id)
                const date = $(this).val()

                localStorage.setItem('_date_{{request()->query('type')}}', date)

                $.ajax({
                    url: '{{route('admin.products.get_latest_stock_by_date')}}',
                    method: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        date,
                        product_ids,
                    },
                    success: function (response) {
                        response.products.forEach(prod => {
                            products.forEach((data, i) => {
                                if(JSON.parse(getValue(data, 'product')).id === prod.product_id) {
                                    const value = JSON.parse(getValue(products[i], 'product'))
                                    value.stock = prod.stock
                                    products[i][0] = {
                                        name: 'product',
                                        value: JSON.stringify(value),
                                    }
                                }
                            })

                        })
                        renderTableProducts()
                    }
                })
            })

            if(localStorage.getItem('_transaction_code_{{request()->query('type')}}')) {
                codeEl.val(localStorage.getItem('_transaction_code_{{request()->query('type')}}'))
            }

            if(localStorage.getItem('_date_{{request()->query('type')}}')) {
                dateEl.val(localStorage.getItem('_date_{{request()->query('type')}}'))
            }

            $('.btn-save').click(function (e) {
                e.preventDefault()

                if(codeEl.val() === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "No DO wajib diisi.",
                    }).then(() => {
                        setTimeout(() => {
                            codeEl.focus()
                        }, 300)
                    })
                    return
                }

                if(dateEl.val() === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Tanggal wajib diisi.",
                    }).then(() => {
                        setTimeout(() => {
                            dateEl.focus()
                        }, 300)
                    })
                    return
                }

                if(products.length <= 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Data barang tidak boleh kosong.",
                    }).then(() => {
                        setTimeout(() => {
                            dateEl.focus()
                        }, 300)
                    })
                    return
                }

                let stockError = false
                products.forEach((product, i) => {
                    // calculate stock
                    let to_stock = parseInt(JSON.parse(getValue(product, 'product'))?.stock || 0, 10) {{ request()->query('type') == 'in' ? '+' : '-' }} parseInt(getValue(product, 'quantity'), 10);
                    to_stock {{ request()->query('type') == 'in' ? '+' : '-' }}= products.filter((prod, j) => {
                        return JSON.parse(getValue(product, 'product'))?.id === JSON.parse(getValue(prod, 'product'))?.id && j < i
                    }).reduce((carry, prod) => {
                        carry += parseInt(getValue(prod, 'quantity'), 10)
                        return carry
                    }, 0)
                    stockError = to_stock < 0
                })
                if(stockError) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Sisa tidak boleh minus.",
                    }).then(() => {
                        setTimeout(() => {
                            dateEl.focus()
                        }, 300)
                    })
                    return
                }

                const spinner = $('<div class="spinner-border spinner-border-sm me-2" role="status"> <span class="visually-hidden">Loading...</span> </div>')
                $(this).prepend(spinner)
                $('.btn-save, .btn-reset, .btn-input').addClass('disabled').attr('disabled', 'disabled')

                const with_print = $(this).hasClass('with-print')

                const code = codeEl.val()
                const date = dateEl.val() || ''
                $.ajax({
                    url: '{{route('admin.transactions.store')}}',
                    method: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        code,
                        date,
                        type: '{{request('type') === 'in' ? 'in' : 'out'}}',
                        products: products.map(p => {
                            return {
                                product_id: JSON.parse(getValue(p, 'product')).id,
                                product_code: getValue(p, 'product_code'),
                                quantity: getValue(p, 'quantity'),
                                note: getValue(p, 'note'),
                            }
                        }),
                        with_print: with_print ? 1 : 0,
                    },
                    success: function ({ redirect_url = null }) {
                        spinner.remove()
                        Swal.fire({
                            icon: "success",
                            title: "Sukses",
                            text: "Transaksi berhasil disimpan",
                        }).then(() => {
                            localStorage.removeItem('_transaction_code_{{request()->query('type')}}')
                            localStorage.removeItem('_products_{{request()->query('type')}}')
                            localStorage.removeItem('_date_{{request()->query('type')}}')
                            window.location = redirect_url || '{{route('admin.transactions.index')}}'
                        })
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: Object.values(response.responseJSON.errors).flat().join(' '),
                        })
                    }
                })

            })
        })
    </script>
@endsection

