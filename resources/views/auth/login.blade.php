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

    <style>
        .container {
            zoom: 1.2;
        }
    </style>

</head>
<body class="bg-body-secondary">
<div id="app">
    <main style="width: 100vw; height: 100vh;" class="d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-body">

                                <h3 class="text-center mb-4 d-flex align-items-center gap-2 justify-content-center">
                                    <img src="{{ asset('/tb-palembang-logo.png') }}" alt="logo" style="height: 44px;"> {{config('app.name')}}
                                </h3>

                                <x-alert></x-alert>

                                <div class="d-flex flex-column gap-3">
                                    <div>
                                        <div class="form-outline" data-mdb-input-init>
                                            <input type="text" id="username" class="form-control form-control-lg" name="username" value="{{old('username')}}" autofocus style="text-transform: uppercase;"/>
                                            <label class="form-label" for="username">Username</label>
                                        </div>
                                        @if($errors->first('username'))
                                            <small class="text-danger">{{ $errors->first('username') }}</small>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="form-outline" data-mdb-input-init>
                                            <input type="password" id="password" class="form-control form-control-lg" name="password"/>
                                            <label class="form-label" for="password">Kata Sandi</label>
                                        </div>
                                        @if($errors->first('password'))
                                            <small class="text-danger">{{ $errors->first('password') }}</small>
                                        @endif
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
                                </div>

                            </div>
                        </div>
                    </form>
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
