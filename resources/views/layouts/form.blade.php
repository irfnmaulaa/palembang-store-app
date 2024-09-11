@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0 text-uppercase">{{$title}}</h2>
                @yield('breadcrumbs')
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-body">
                @yield('form')
            </div>
        </div>
    </div>
@endsection
