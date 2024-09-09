@extends('layouts.form', [
    'title' => (@$item ? 'Edit' : 'Tambah') . ' ' . $action
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
                          <div class="form-group">
                              <label for="product">Pilih Barang</label>
                              <select name="product" id="product" class="form-control select-product">
                              </select>
                          </div>

                          <x-textfield label="Quantity" name="quantity" type="number"></x-textfield>

                          <x-textarea label="Keterangan" name="note"></x-textarea>

                          <div class="mt-2 d-flex gap-3">
                              <button type="submit" class="btn btn-outline-primary btn-lg w-100">Tambahkan Barang</button>
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
                                    <x-textfield label="Tanggal" name="date" type="date" :item="$item"></x-textfield>
                                    <x-textfield label="No DO" name="code" type="text" :item="$item"></x-textfield>
                                </div>
                            </div>
                            <table class="table-bordered table-hover table mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center bg-body-tertiary">No</th>
                                    <th class="bg-body-tertiary">Nama Barang / Variant</th>
                                    <th class="bg-body-tertiary">Kode Barang</th>
                                    <th style="width: 100px;" class="text-center bg-body-tertiary">Quantity</th>
                                    <th class="bg-body-tertiary">Keterangan</th>
                                    <th class="bg-body-tertiary text-center">Stok Saat Ini</th>
                                    <th style="width: 80px;" class="text-center bg-body-tertiary">Aksi</th>
                                </tr>
                                </thead>
                                <tbody id="table-body-products">
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="d-flex gap-3">
{{--                                <a href="" class="btn btn-outline-success btn-print btn-lg"><i class="fas fa-print me-2"></i> Cetak Tabel</a>--}}
                                <a href="" class="btn btn-outline-danger btn-reset btn-lg">Kosongkan Tabel</a>
                            </div>
                            <a href="" class="btn btn-primary btn-lg btn-save">Simpan {{$action}}</a>
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
                        return query;
                    }
                },
            } );

            let products = []
            if(localStorage.getItem('_products')) {
                products = JSON.parse(localStorage.getItem('_products'))
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

                if(parseInt(JSON.parse(getValue(product, 'product'))?.stock || 0, 10) {{ request('type') === 'in' ? '+' : '-' }} parseInt(getValue(product, 'quantity'), 10) < 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Quantity melebihi jumlah stock barang",
                    });
                    return
                }

                // console.log(JSON.parse(getValue(product, 'product'))?.id)
                const prodIndex = products.findIndex(p => JSON.parse(getValue(p, 'product'))?.id == JSON.parse(getValue(product, 'product'))?.id)
                if(prodIndex > -1) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-outline-danger btn-lg ms-2",
                            cancelButton: "btn btn-danger btn-lg"
                        },
                        buttonsStyling: false
                    });
                    swalWithBootstrapButtons.fire({
                        title: "Barang sudah dimasukan",
                        text: "Apakah ingin memperbarui datanya?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Ya, perbarui",
                        cancelButtonText: "Tidak",
                        reverseButtons: true
                    }).then((result) => {
                        if ( result.isConfirmed ) {
                            products[prodIndex] = product
                            renderTableProducts()

                            $(this)[0].reset()
                            $( '.select-product' ).val(null).trigger('change')
                        }
                    });
                } else {
                    products.push(product)
                    renderTableProducts()

                    $(this)[0].reset()
                    $( '.select-product' ).val(null).trigger('change')
                }

            })
            renderTableProducts()

            function renderTableProducts() {
                const tbody = $('#table-body-products')
                tbody.html('')

                localStorage.setItem('_products', JSON.stringify(products))

                if(products.length > 0) {
                    products.forEach((product, i) => {
                        const className = '{{request()->query('type') == 'in' ? 'text-primary fw-bold' : 'text-danger fw-bold'}}';
                        const trInit = `
                        <tr>
                            <td class="${className} text-center">
                                ${ i+1 }
                            </td>
                            <td class="${className}">
                                ${ JSON.parse(getValue(product, 'product'))?.name || '-' }
                            </td>
                            <td class="${className}">
                                ${ JSON.parse(getValue(product, 'product'))?.code || '-' }
                            </td>
                            <td class="${className} text-center">
                                ${ getValue(product, 'quantity') }
                            </td>
                            <td class="${className}">
                                ${ getValue(product, 'note') }
                            </td>
                            <td class="text-center ${className}">
                                ${ parseInt(JSON.parse(getValue(product, 'product'))?.stock || 0, 10)  }
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
                        confirmButton: "btn btn-outline-danger btn-lg ms-2",
                        cancelButton: "btn btn-danger btn-lg"
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
                    reverseButtons: true
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
                        confirmButton: "btn btn-outline-danger btn-lg ms-2",
                        cancelButton: "btn btn-danger btn-lg"
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
                    reverseButtons: true
                }).then((result) => {
                    if ( result.isConfirmed ) {
                        products = []
                        renderTableProducts()
                    }
                });
            })

            const codeEl = $('input[name="code"]')
            const dateEl = $('input[name="date"]')

            codeEl.change(function() {
                localStorage.setItem('_transaction_code', codeEl.val())
            })

            if(localStorage.getItem('_transaction_code')) {
                codeEl.val(localStorage.getItem('_transaction_code'))
            }

            $('.btn-save').click(function (e) {
                e.preventDefault()

                if(codeEl.val() === '') {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "No DO wajib diisi",
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
                        text: "Tanggal wajib diisi",
                    }).then(() => {
                        setTimeout(() => {
                            dateEl.focus()
                        }, 300)
                    })
                    return
                }

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-primary btn-lg ms-2",
                        cancelButton: "btn btn-outline-primary btn-lg"
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "Konfirmasi",
                    text: "Klik proses untuk menyimpan data",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Proses",
                    cancelButtonText: "Batalkan",
                    reverseButtons: true
                }).then((result) => {
                    const code = codeEl.val()
                    const date = dateEl.val()
                    if ( result.isConfirmed ) {
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
                                        quantity: getValue(p, 'quantity'),
                                        note: getValue(p, 'note'),
                                    }
                                })
                            },
                            success: function () {
                                Swal.fire({
                                    icon: "success",
                                    title: "Sukses",
                                    text: "Transaksi berhasil disimpan",
                                }).then(() => {
                                    localStorage.removeItem('_transaction_code')
                                    localStorage.removeItem('_products')
                                    window.location = '{{route('admin.transactions.index')}}'
                                })
                            },
                            error: function () {
                                Swal.fire({
                                    icon: "error",
                                    title: "Gagal",
                                    text: "Transaksi gagal disimpan. Coba sekali lagi.",
                                })
                            }
                        })
                    }
                });
            })
        })
    </script>
@endsection

