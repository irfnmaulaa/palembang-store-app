@extends('layouts.form', [
    'title' => (@$item ? 'Edit' : 'Tambah') . ' User',
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.users.' . (@$item ? 'update' : 'store'), [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf
            @if(@$item)
                @method('PUT')
            @endif

            <x-textfield label="Nama Lengkap" name="name" type="text" :item="$item" autofocus></x-textfield>
            <x-textfield label="Username" name="username" type="text" :item="$item"></x-textfield>

            @if(@!$item)
                <x-textfield label="Password" name="password" type="password" :item="$item"></x-textfield>
            @endif

            <div class="form-group">
                <label for="role">Hak akses</label>
                <select name="role" id="role" class="form-control form-control-lg {{$errors->first('role') ? 'border-danger' : ''}}">
                    <option value="">Pilih hak akses</option>
                    <option value="super" {{@$item->role === 'super' || old('role') === 'super' ? 'selected' : ''}}>Super Admin</option>
                    <option value="admin" {{@$item->role === 'admin' || old('role') === 'admin' ? 'selected' : ''}}>Admin Toko</option>
                    <option value="staff" {{@$item->role === 'staff' || old('role') === 'staff' ? 'selected' : ''}}>Admin Gudang</option>
                </select>
                @if($errors->first('role'))
                    <small class="text-danger mb-0">{{$errors->first('role')}}</small>
                @endif
            </div>

            <div class="mt-2 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                <a href="{{route('admin.users.index')}}" class="btn btn-outline-primary btn-lg">Batalkan</a>
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.users.index')}}">Data User</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{@$item ? 'Edit' : 'Tambah'}} User
    </div>
@endsection


