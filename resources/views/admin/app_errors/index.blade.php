@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0 text-uppercase">Redundant Error Checker</h2>
                <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                    <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                    Redundant Error Checker
                </div>
            </div>
            <div class="d-flex gap-3">
                <a href="" class="btn btn-primary btn-lg btn-rec-check">
                    Cek Error
                </a>
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-body">
                <!-- Tabs navs -->
                <ul class="nav nav-tabs mb-3" id="ex1" role="tablist">
                    @foreach($errors as $errorName => $value)
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link {{ $errorName == $errorType ? 'active' : '' }}"
                            href="{{ route('admin.app_errors.index', ['error_type' => $errorName]) }}"
                            role="tab"
                        >
                            {{ $value['label'] }} ({{ $value['data']->total() }})
                        </a>
                    </li>
                    @endforeach

                </ul>
                <!-- Tabs navs -->
            </div>
            <div class="card-body py-0 d-flex flex-column gap-4">

                @include('admin.app_errors.table.' . $errorType, ['data' => $errors[$errorType]['data']])

            </div>
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    {{ $errors[$errorType]['data']->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>

        <div class="text-muted"><small>Ditampilkan {{number_format($errors[$errorType]['data']->firstItem(), 0, ',', '.')}} - {{number_format($errors[$errorType]['data']->count() - 1 + $errors[$errorType]['data']->firstItem(), 0, ',', '.')}} dari {{number_format($errors[$errorType]['data']->total(), 0, ',', '.')}} data</small></div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.btn-rec-check').click(function (e) {
                e.preventDefault()

                $(this).addClass('disabled').html('Checking...')
                $.ajax({
                    method: 'POST',
                    data: {
                      _token: '{{csrf_token()}}'
                    },
                    url: "{{route('admin.app_errors.check')}}",
                    success: function () {
                        location.reload()
                    },
                    error: function () {
                        alert('Ops, there are something error')
                        // location.reload()
                    }
                })

            })
        })
    </script>
@endsection
