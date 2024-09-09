<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        rel="stylesheet"
    />
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
        rel="stylesheet"
    />
    <!-- MDB -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css"
        rel="stylesheet"
    />

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- MDB -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
        body {
            font-size: 1.1rem;
        }
        td, th {
            vertical-align: middle;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div id="app">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
            <!-- Container wrapper -->
            <div class="container-fluid">
                <!-- Navbar brand -->
                <a class="navbar-brand me-2" href="{{route('admin.dashboard')}}">
                    {{config('app.name')}}
                </a>

                <!-- Toggle button -->
                <button
                    data-mdb-collapse-init
                    class="navbar-toggler"
                    type="button"
                    data-mdb-target="#navbarButtonsExample"
                    aria-controls="navbarButtonsExample"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Collapsible wrapper -->
                <div class="collapse navbar-collapse" id="navbarButtonsExample">
                    <!-- Left links -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @if(in_array(auth()->user()->role, ['admin', 'staff', 'super']))
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.transactions.index')}}">Transaksi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.histories.index')}}">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.products.index')}}">Barang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.categories.index')}}">Kategori</a>
                        </li>
                        @endif

                        @if(in_array(auth()->user()->role, ['super']))
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.users.index')}}">Pengguna</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.settings.index')}}">Pengaturan</a>
                        </li>
                        @endif
                    </ul>
                    <!-- Left links -->

                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('admin.products.index')}}">
                                {!! auth()->user()->role_display !!} {{auth()->user()->name}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="document.getElementById('form-logout').submit()">Logout</a>
                            <form action="{{route('logout')}}" id="form-logout" method="POST">@csrf</form>
                        </li>
                    </ul>
                </div>
                <!-- Collapsible wrapper -->
            </div>
            <!-- Container wrapper -->
        </nav>
        <!-- Navbar -->

        <main class="py-4">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>

{{--        <footer class="text-center py-3 bg-body-tertiary fs-6 text-muted mt-4">--}}
{{--            Copyright &copy; 2024, Ahmad Irfan Maulana--}}
{{--        </footer>--}}
    </div>

    <script
        type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"
    ></script>
    <script>

        $( '.select-2' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
        } );


        $('body').delegate('.btn-delete', 'click', function (e) {
            e.preventDefault()

            const url = $(this).attr('href')

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
                    $.ajax({
                        method: 'DELETE',
                        data: {
                            _token: '{{csrf_token()}}',
                            _method: 'PUT',
                        },
                        url,
                        success: function () {
                            swalWithBootstrapButtons.fire({
                                title: "Berhasil",
                                text: "Data berhasil dihapus",
                                icon: "success"
                            }).then(result => {
                                location.reload()
                            })
                        },
                        error: function () {
                            swalWithBootstrapButtons.fire({
                                title: "Gagal",
                                text: "Data gagal dihapus karna terdapat data dari tabel lain yang berelasi dengan data ini atau hak akses tidak diperbolehkan.",
                                icon: "error"
                            })
                        }
                    })

                }
            });
        })
    </script>
    @yield('js')
</body>
</html>
