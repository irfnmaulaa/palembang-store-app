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

    <!-- MDB -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css"
        rel="stylesheet"
    />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        * {
            font-family: "Rubik", sans-serif;
            font-optical-sizing: auto;
        }
        table * {
            text-transform: uppercase;
        }
        body {
            font-size: 1.1rem;
        }
        .table th {
            font-weight: bold;
        }
        td, th {
            vertical-align: middle;
        }
        .card-item:hover {
            cursor: grab;
        }
        .card-item:active {
            cursor: grabbing;
        }
        .btn:focus {
            outline: 3px solid #0653da;
        }
        a:not(.btn):hover {
            text-decoration: underline;
        }
    </style>

    <!-- MDB -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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

                        @foreach(collect(get_menus())->filter(function ($menu) { return in_array(auth()->user()->role, $menu->allowed_roles); })->all() as $menu)
                            <li class="nav-item">
                                <a class="nav-link" href="{{route($menu->link)}}">
                                    {{$menu->label}}
                                </a>
                            </li>
                        @endforeach

                    </ul>
                    <!-- Left links -->

                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
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
            const withPin = $(this).hasClass('with-pin')

            let additionalConfig = {}
            if (withPin) {
                additionalConfig = {
                    input: 'password',
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
                                resolve({ pin })
                            },
                            error: ({responseJSON}) => {
                                Swal.showValidationMessage(responseJSON.message || `Pin tidak valid`);
                                resolve()
                            },
                        })
                    }),
                    allowOutsideClick: () => !Swal.isLoading()
                }
            }

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-danger btn-lg",
                    cancelButton: "btn btn-outline-danger btn-lg ms-3"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: withPin ? "Konfirmasi penghapusan" : "Apakah kamu yakin ingin menghapus?",
                text: withPin ? "Masukan PIN untuk menghapus data" : "Data yang telah dihapus tidak bisa dikembalikan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: withPin ? "Hapus data" : "Ya, hapus",
                cancelButtonText: "Tidak",
                ...additionalConfig,
            }).then((result) => {
                if ( result.isConfirmed ) {
                    $.ajax({
                        method: 'POST',
                        data: {
                            _token: '{{csrf_token()}}',
                            _method: 'DELETE',
                            pin: result.value.pin,
                        },
                        url,
                        success: function (response) {
                            swalWithBootstrapButtons.fire({
                                title: "Berhasil",
                                text: "Data berhasil dihapus",
                                icon: "success"
                            }).then(result => {
                                window.location = response.redirect_url
                            })
                        },
                        error: function ({ responseJSON }) {
                            const message = responseJSON?.message || 'Data gagal dihapus karna terdapat data dari tabel lain yang berelasi dengan data ini atau hak akses tidak diperbolehkan.'
                            swalWithBootstrapButtons.fire({
                                title: "Gagal",
                                text: message,
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
