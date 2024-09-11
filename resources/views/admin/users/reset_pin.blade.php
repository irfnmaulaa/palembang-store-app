@extends('layouts.form', [
    'title' => 'Reset Pin'
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.users.reset_pin', [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf

            <h4>Pengguna: {{$item->name}} ({{$item->username}}) - {!! $item->role_display !!}</h4>
            <x-textfield label="Pin baru" name="pin" type="password"></x-textfield>
            <x-textfield label="Ketik ulang pin baru" name="pin_confirmation" type="password"></x-textfield>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Reset Pin</button>
                <a href="{{route('admin.users.index')}}" class="btn btn-outline-primary btn-lg">Batalkan</a>
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.users.index')}}">Data User</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        Reset Pin
    </div>
@endsection


