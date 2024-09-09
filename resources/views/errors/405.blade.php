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

</head>
<body class="bg-body-secondary">
<div id="app">
    <main style="width: 100vw; height: 100vh;" class="d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <h2 class="text-center mb-4">
                    {{config('app.name')}}
                </h2>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            Akun anda tidak dapat masuk ke dalam sistem. Hubungi admin untuk aktivasi.
                        </div>
                        <div class="card-body pt-0 text-center">
                            <a href="" class="btn btn-lg btn-primary" onclick="event.preventDefault(); document.getElementById('form-logout').submit()">Kembali</a>
                            <form action="{{route('logout')}}" id="form-logout" method="POST">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- MDB -->
<script
    type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"
></script>
</body>
</html>
