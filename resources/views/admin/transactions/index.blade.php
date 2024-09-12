@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-5">

        {{-- Pending Transactions --}}
        <div id="pending-transactions-wrap" class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                    <div class="d-flex">
                        <h2 class="mb-0">TRANSAKSI <span class="text-warning">PENDING</span></h2>
                    </div>
                    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                        Data Transaksi
                    </div>
                </div>
                <div class="d-flex gap-3" style="white-space: nowrap;">
                    <div class="d-flex gap-3">
                        <a href="{{route('admin.transactions.create', ['type' => 'in'])}}" class="btn btn-danger btn-lg">
                            Barang Masuk
                        </a>
                        <a href="{{route('admin.transactions.create', ['type' => 'out'])}}" class="btn btn-dark btn-lg">
                            Barang Keluar
                        </a>
                    </div>
                    @if(auth()->user()->role == 'admin')
                        <form action="" style="border-left: 1px solid #ddd;" class="ps-3">
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="page2" value="1">
                            @foreach(request()->except(['keyword', 'keyword2', 'page', 'page2', 'date_range']) as $key => $value)
                                <input type="hidden" name="{{$key}}" value="{{$value}}">
                            @endforeach
                            <div class="input-group input-group-lg">
                                <input type="text" name="date_range" class="form-control form-control-lg date-range-picker" style="min-width: 250px">
                                <button class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <x-alert></x-alert>

            <div class="card border shadow-none overflow-hidden">
                <div class="card-body py-4">
                    <form action="{{ route('admin.transactions.verify') }}" id="form-verify" method="POST">
                        @csrf
                        @method('PUT')
                        <table class="table table-responsive table-sm table-bordered table-hover mb-0">
                            <thead>
                            <tr>
                                @if(auth()->user()->role === 'admin')
                                <th class="bg-body-tertiary" rowspan="2" style="width: 50px;">
                                    <div class="d-flex justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="check_all" />
                                        </div>
                                    </div>
                                </th>
                                @endif
                                <th class="bg-body-tertiary" rowspan="2">Tanggal</th>
                                <th class="bg-body-tertiary" rowspan="2">No DO</th>
                                <th colspan="7" class="bg-body-tertiary text-center">Barang</th>
                            </tr>
                            <tr>
                                <th class="bg-body-tertiary text-center" style="width: 120px">Quantity</th>
                                <th class="bg-body-tertiary">Nama Barang</th>
                                <th class="bg-body-tertiary">Kode Barang</th>
                                <th class="bg-body-tertiary">Keterangan</th>
                                <th class="bg-body-tertiary text-center" style="width: 120px">Sisa</th>
                                <th class="bg-body-tertiary text-center">ID</th>
                            </tr>
                            </thead>
                            <tbody class="table-body">
                            @foreach($transactions_pending as $i => $tp)
                                @php
                                    $count = $tp->transaction_products()->where('is_verified', 0)->count() + 1;
                                    $products = $tp->products()->wherePivot('is_verified', 0)->get();
                                    $className = get_table_row_classname($tp->type);
                                @endphp
                                <tr>
                                    @if(auth()->user()->role === 'admin')
                                    <td rowspan="{{$count}}" class="text-center {{$className}}">

                                    </td>
                                    @endif
                                    <td rowspan="{{$count}}" class="{{$className}}">
                                        <label for="tp-{{$tp->id}}" class="d-flex align-items-center">
                                            {{\Carbon\Carbon::parse($tp->date)->format('d/m/Y')}}
                                        </label>
                                    </td>
                                    <td rowspan="{{$count}}" class="{{$className}}">
                                        <label for="tp-{{$tp->id}}" class="d-flex align-items-center d-flex gap-2 align-items-center">
                                            @if(auth()->user()->role === 'admin')
                                            <div class="form-check">
                                                <input class="form-check-input transaction-checkbox" type="checkbox" value="" id="tp-{{$tp->id}}" />
                                            </div>
                                            @endif
                                            {{$tp->code}}
                                        </label>
                                    </td>
                                </tr>
                                @foreach($products as $product)
                                    <tr>
                                        <td class="{{$className}} text-center">
                                            <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                                                {{$product->pivot->quantity}}
                                            </label>
                                        </td>
                                        <td class="{{$className}}">
                                            <label for="product-{{$product->id}}" class="d-flex gap-2 align-items-center">
                                                @if(auth()->user()->role === 'admin')
                                                <div class="form-check">
                                                    <input data-parent="tp-{{$tp->id}}" class="form-check-input product-checkbox" type="checkbox" name="transaction_product_ids[]" value="{{$product->pivot->id}}" id="product-{{$product->id}}" />
                                                </div>
                                                @endif
                                                {{$product->name}} {{$product->variant}}
                                            </label>
                                        </td>
                                        <td class="{{$className}}">
                                            <label for="product-{{$product->id}}" class="d-flex align-items-center">
                                                {{$product->code}}
                                            </label>
                                        </td>
                                        <td class="{{$className}}">
                                            <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                                                {{$product->pivot->note}}
                                            </label>
                                        </td>
                                        <td class="{{$className}} text-center">
                                            <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                                                {{$product->pivot->to_stock}} {{$product->unit}}
                                            </label>
                                        </td>
                                        <td class="{{$className}}">
                                            <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                                                @if($tp->creator)
                                                    {{$tp->creator->name}}
                                                @endif
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(count($transactions_pending) == 0)
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-3">
                            @if(auth()->user()->role === 'admin')
                                <button type="submit" form="form-verify" class="btn btn-success btn-lg btn-verify disabled">Verifikasi</button>
                                <button type="submit" form="form-verify" name="delete" value="1" class="btn btn-outline-danger btn-reject btn-lg disabled">Hapus</button>
                            @elseif(auth()->user()->role === 'staff')
                                <button type="button" class="btn btn-outline-dark btn-lg btn-close-store">Tutup Gudang <i class="fas fa-sign-out ms-2"></i></button>
                                <form action="{{ route('admin.users.close_store') }}" id="form-close-store" method="POST">
                                    @csrf
                                </form>
                            @endif
                        </div>
                        <div class="table-pagination">
                            {{ $transactions_pending->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-summary">
                @if(count($transactions_pending) > 0)
                    <div class="text-muted"><small>Ditampilkan {{number_format($transactions_pending->firstItem(), 0, ',', '.')}} - {{number_format($transactions_pending->count() - 1 + $transactions_pending->firstItem(), 0, ',', '.')}} dari {{number_format($transactions_pending->total(), 0, ',', '.')}} data</small></div>
                @endif
            </div>
        </div>

        {{-- Verified Transactions --}}
        <div id="verified-transactions-wrap" class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex flex-column gap-1">
                    <h2 class="mb-0">TRANSAKSI <span class="text-success">TERVERIFIKASI</span></h2>
                </div>
            </div>

            <div class="card border shadow-none overflow-hidden">
                <div class="table-body card-body py-0 pt-4">
                <x-verified-transactions-table :transactions="$transactions_verified"></x-verified-transactions-table>
                </div>
                <div class="card-body">
                    <div class="table-pagination d-flex justify-content-center align-items-center gap-2">
                        {{ $transactions_verified->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>

            <div class="table-summary">
                @if(count($transactions_verified) > 0)
                    <div class="text-muted"><small>Ditampilkan {{number_format($transactions_verified->firstItem(), 0, ',', '.')}} - {{number_format($transactions_verified->count() - 1 + $transactions_verified->firstItem(), 0, ',', '.')}} dari {{number_format($transactions_verified->total(), 0, ',', '.')}} data</small></div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        $(document).ready(function() {

            @if(config('app.debug'))
            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = true;
            @endif

            var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}'
            });

            var channel = pusher.subscribe('page-refresh');
            channel.bind('refresh-triggered', function(data) {
                if(data.user.username !== '{{ auth()->user()->username }}') {
                    getTransactions(data.message)
                }
            });

            const transactionsPendingWrap = $('#pending-transactions-wrap')
            const transactionsVerifiedWrap = $('#verified-transactions-wrap')

            function getTransactions(message = '') {
                const url = '{!! request()->fullUrl() !!}'
                $.ajax({
                    url,
                    method: 'GET',
                    success: function ({ transactions_pending, transactions_verified }) {
                        Swal.fire({
                            text: message || 'Terdapat transaksi baru yang perlu direview',
                            icon: "success",
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                        })
                        renderTransactionsPending(transactions_pending)
                        renderTransactionsVerified(transactions_verified)
                    }
                })
            }

            function renderTransactionsPending(transactions) {
                const tbody = transactionsPendingWrap.find('.table-body');
                const pagination = transactionsPendingWrap.find('.table-pagination');
                const summary = transactionsPendingWrap.find('.table-summary');
                tbody.html(transactions.table)
                pagination.html(transactions.pagination)
                summary.html(transactions.summary)
            }

            function renderTransactionsVerified(transactions) {
                const tbody = transactionsVerifiedWrap.find('.table-body');
                const pagination = transactionsVerifiedWrap.find('.table-pagination');
                const summary = transactionsVerifiedWrap.find('.table-summary');
                tbody.html(transactions.table)
                pagination.html(transactions.pagination)
                summary.html(transactions.summary)
            }

            $('body').delegate('#check_all', 'change', function() {
                if($(this).is(':checked')) {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', true).trigger('change');
                } else {
                    $(this).parents('table').find('tbody input[type="checkbox"]').prop('checked', false).trigger('change');
                }
            })

            $('body').delegate('.product-checkbox', 'change', function () {
                const parent = $(this).data('parent')
                if($(`input[data-parent=${ parent }]:checked`).length >= $(`input[data-parent=${ parent }]`).length) {
                    $(`#${parent}`).prop('checked', true)
                } else {
                    $(`#${parent}`).prop('checked', false)
                }
            })

            $('body').delegate('.transaction-checkbox', 'change', function () {
                const parent = $(this).attr('id')
                if($(this).is(':checked')) {
                    $(`input[data-parent=${parent}]`).prop('checked', true)
                } else {
                    $(`input[data-parent=${parent}]`).prop('checked', false)
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
                    $('.btn-verify, .btn-reject').removeClass('disabled')
                } else {
                    $('.btn-verify, .btn-reject').addClass('disabled')
                }
            })

            $('.btn-reject, .btn-verify').click(function(e) {
                e.preventDefault()

                const isForDelete = $(e.target).hasClass('btn-reject')

                if(isForDelete) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: `btn btn-danger btn-lg me-3`,
                            cancelButton: `btn btn-outline-danger btn-lg`
                        },
                        buttonsStyling: false
                    });
                    swalWithBootstrapButtons.fire({
                        title: "Konfirmasi hapus",
                        input: 'password',
                        text: 'Masukan PIN untuk dapat menghapus transaksi',
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: 'Hapus Transaksi',
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
                                    $('#form-verify').prepend(pinInput)
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
                            const deleteForm = $(`<input type="hidden" name="delete" value="1"/>`)
                            $('#form-verify').append(deleteForm).submit()
                        }
                    });
                } else {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: `btn btn-success btn-lg me-3`,
                            cancelButton: `btn btn-outline-success btn-lg`
                        },
                        buttonsStyling: false
                    });
                    swalWithBootstrapButtons.fire({
                        title: "Konfirmasi Verifikasi",
                        text: 'Masukan PIN untuk dapat memverifikasi transaksi',
                        input: "password",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: 'Verifikasi Transaksi',
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
                                    $('#form-verify').prepend(pinInput)
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
                            $('#form-verify').submit()
                        }
                    });
                }
            })

            $('.btn-close-store').click(function (e) {
                e.preventDefault()

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: `btn btn-dark btn-lg me-3`,
                        cancelButton: `btn btn-outline-dark btn-lg`
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "Konfirmasi tutup gudang",
                    text: 'Apakah kamu yakin ingin tutup gudang?',
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: 'Ya, tutup gudang',
                    cancelButtonText: "Batalkan",
                }).then((result) => {
                    if(result.isConfirmed) {
                        $('#form-close-store').submit()
                        setTimeout(() => {
                            window.location = '{{route('login')}}'
                        }, 500)
                    }
                });

            })

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
        })

        @if(request()->has('with_print') && request()->has('transaction_id'))
            window.location = '{{ route('admin.transactions.export_per_transaction', ['transaction' => request()->query('transaction_id')]) }}'
            setTimeout(() => {
                window.location = '{{ route('admin.transactions.index') }}'
            }, 500)
        @endif
    </script>
@endsection
