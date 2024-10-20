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
        body {
            zoom: 1.25;
            overflow-x: hidden;
        }
        .cta-wrap .btn {
            zoom: 1.4;
        }
        .cta-wrap .dropdown-menu.show {
            transform: translate3d(-1px, 71px, 0px) !important;
        }
        .navbar, table * {
            text-transform: uppercase;
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
        .navbar-brand {
            font-weight: 500;
            padding-right: 0.6rem;
        }
        .navbar-nav .nav-link.active, .navbar-nav .nav-link.show {
            font-weight: 500;
        }
        .daterangepicker {
            left: 0;
            top: 0;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options:not(.select2-results__options--nested) {
            max-height: 14.5rem;
        }
        .nav-tabs .nav-link {
            font-size: unset;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .loading-container {
            width: 100%;
            height: 4px;
            background-color: #f3f3f3;
            position: absolute;
            top: 0;
            left: 0;
            overflow: hidden;
            transform: translateY(-100%);
        }

        .loading-bar {
            width: 50%;
            height: 100%;
            background-color: #4285F4;
            position: absolute;
            animation: loading 1.3s infinite linear;
        }

        @keyframes loading {
            0% {
                left: -50%;
                width: 50%;
            }
            100% {
                width: 50%;
                left: 100%;
            }
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

        <!-- S: Navbar -->
        <x-navbar></x-navbar>
        <!-- E: Navbar -->

        <!-- S: Main -->
        <main class="py-4">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
        <!-- E: Main -->


        <!-- S: Footer -->
        <footer class="text-center py-3">
            <small>&copy; 2024, <a href="mailto:irfnmaulaa@gmail.com">Programmer Subang</a></small>
        </footer>
        <!-- E: Footer -->
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
            dropdownParent: $('.select-2').parents('.form-group')
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
    @yield('advance_js')
</body>
</html>
