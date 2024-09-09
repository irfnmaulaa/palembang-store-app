@extends('layouts.form', [
    'title' => 'Reset Password'
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.users.reset_password', [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf

            <h4>Pengguna "{{$item->name}} ({{$item->username}})" - {!! $item->role_display !!}</h4>
            <x-textfield label="Password baru" name="password" type="password"></x-textfield>
            <x-textfield label="Ketik ulang password baru" name="password_confirmation" type="password"></x-textfield>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
                <a href="{{route('admin.users.index')}}" class="btn btn-outline-primary btn-lg">Batalkan</a>
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.users.index')}}">Data User</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Reset Password
    </div>
@endsection


