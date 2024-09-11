@extends('layouts.index', [
    'title' => 'Data Pengguna',
    'data' => $users,
    'order_options' => $order_options,
    'withNoSearch' => true,
    'withNoOrder' => true,
])

@section('table')
    <x-alert></x-alert>
    <table class="table table-users table-hover mb-0 table-sm">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nama Pengguna</th>
            <th>Hak Akses</th>
            <th>Status</th>
            <th style="width: 350px" class="text-start">Aktivasi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $i => $user)
            <tr>
                <td>{{$user->name}}</td>
                <td>
                    <a href="{{route('admin.users.edit', [$user])}}">
                        {{$user->username}}
                    </a>
                </td>
                <td>
                    {!! $user->role_display !!}
                </td>
                <td>
                    {!! $user->status_display !!}
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-start gap-2">
                        @if($user->role !== 'admin')
                        <a href="" onclick="event.preventDefault(); document.getElementById('form-activate-{{$user->id}}').submit()" data-mdb-tooltip-init data-mdb-html="true" title='{{$user->is_active ? 'Nonaktifkan' : 'Aktifkan'}} <br/> "{{$user->name}}"' class="btn p-2 shadow-none border btn-lg {{$user->is_active ? '' : 'btn-success'}} d-flex align-items-center gap-2">
                            {{$user->is_active ? 'Nonaktifkan' : 'Aktifkan'}}
                        </a>
                        @endif
                    </div>
                </td>
            </tr>

            <form id="form-activate-{{$user->id}}" action="{{route('admin.users.activate', [$user])}}" method="POST">
                @csrf
            </form>
        @endforeach
        </tbody>
    </table>
@endsection

@section('cta')
    <div class="d-flex gap-3">
        <a href="{{route('admin.users.create')}}" class="btn btn-primary btn-lg">
            Tambah Pengguna
        </a>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i> Data Pengguna
    </div>
@endsection
