@extends('layouts.index', [
    'title' => 'Data Pengguna',
    'data' => $users,
    'order_options' => $order_options
])

@section('table')
    <x-alert></x-alert>
    <table class="table table-users table-striped mb-0 table-sm">
        <thead>
        <tr>
            <th style="width: 80px" class="text-center">No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Hak Akses</th>
            <th>Status</th>
            <th style="width: 350px" class="text-start">Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $i => $user)
            <tr>
                <td class="text-center">{{$users->firstItem() + $i}}</td>
                <td>{{$user->name}}</td>
                <td>{{$user->username}}</td>
                <td>
                    {!! $user->role_display !!}
                </td>
                <td>
                    {!! $user->status_display !!}
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-start gap-2">
                        <a href="{{route('admin.users.destroy', [$user])}}" data-mdb-tooltip-init data-mdb-html="true" title='Hapus Pengguna <br/> "{{$user->name}}"' class="btn px-3 py-2 shadow-none border btn-lg d-flex align-items-center gap-2 btn-delete {{$user->role === 'super' ? 'disabled' : ''}}">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="{{route('admin.users.edit', [$user])}}" data-mdb-tooltip-init data-mdb-html="true" title='Edit Pengguna <br/> "{{$user->name}}"' class="btn px-3 py-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{route('admin.users.reset_password', [$user])}}" data-mdb-tooltip-init data-mdb-html="true" title='Reset Kata Sandi <br/> "{{$user->name}}"' class="btn px-3 py-2 shadow-none border btn-lg d-flex align-items-center gap-2">
                            <i class="fas fa-key"></i>
                        </a>
                        <a href="" onclick="event.preventDefault(); document.getElementById('form-activate-{{$user->id}}').submit()" data-mdb-tooltip-init data-mdb-html="true" title='{{$user->is_active ? 'Nonaktifkan' : 'Aktifkan'}} <br/> "{{$user->name}}"' class="btn p-2 shadow-none border btn-lg {{$user->is_active ? '' : 'btn-success'}} d-flex align-items-center gap-2">
                            {{$user->is_active ? 'Nonaktifkan' : 'Aktifkan'}}
                        </a>
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
        <x-export-button table="users"></x-export-button>
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
