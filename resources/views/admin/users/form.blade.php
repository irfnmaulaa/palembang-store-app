@extends('layouts.form', [
    'title' => (@$item ? 'Edit' : 'Tambah') . ' Pengguna',
])

@section('form')
    <div>
        <x-alert></x-alert>

        <form action="{{route('admin.users.' . (@$item ? 'update' : 'store'), [$item])}}" class="d-flex flex-column gap-3" method="POST">
            @csrf
            @if(@$item)
                @method('PUT')
            @endif

            <x-textfield label="Nama Pengguna" name="username" type="text" :item="$item" autofocus></x-textfield>
            <x-textfield label="ID" name="name" type="text" :item="$item"></x-textfield>

            @if(@!$item)
                <x-textfield label="Password" name="password" type="password" :item="$item"></x-textfield>
            @endif

            <div class="form-group">
                <label for="role">Hak akses</label>
                <select name="role" id="role" class="form-control form-control-lg {{$errors->first('role') ? 'border-danger' : ''}}">
                    <option value="staff" {{@$item ? ($item->role === 'staff' || old('role') === 'staff' ? 'selected' : '') : 'selected' }}>User</option>
                    <option value="admin" {{@$item->role === 'admin' || old('role') === 'admin' ? 'selected' : ''}}>Administrator</option>
                </select>
                @if($errors->first('role'))
                    <small class="text-danger mb-0">{{$errors->first('role')}}</small>
                @endif
            </div>

            @if(@!$item)
            <div class="form-group input-pin-wrap {{old('role') !== 'admin' ? 'd-none' : ''}}">
                <x-textfield label="Pin" name="pin" type="password" :item="$item"></x-textfield>
            </div>
            @endif

            <div class="form-group d-flex justify-content-between mt-2">
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
                    <a href="{{route('admin.users.index')}}" class="btn btn-outline-primary btn-lg">Batalkan</a>
                </div>

                @if(@$item)
                <div class="d-flex gap-3">
                    <a href="{{route('admin.users.reset_password', [$item])}}" class="btn btn-outline-info btn-lg">
                        <i class="fas fa-key me-1"></i> Reset Password
                    </a>
                    @if($item->role === 'admin')
                    <a href="{{route('admin.users.reset_pin', [$item])}}" class="btn btn-outline-info btn-lg">
                        <i class="fas fa-lock-open me-1"></i> Reset Pin
                    </a>
                    @endif
                    @if(auth()->user()->id != $item->id)
                    <a href="{{route('admin.users.destroy', [$item])}}" class="btn btn-delete with-pin btn-outline-danger btn-lg">
                        <i class="fas fa-trash me-1"></i> Hapus Pengguna
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('breadcrumbs')
    <div class="d-flex align-items-center gap-2 fs-6 text-muted">
        <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        <a href="{{route('admin.users.index')}}">Data Pengguna</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
        {{@$item ? 'Edit' : 'Tambah'}} Pengguna
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('[name="role"]').change(function () {
                if($(this).val() === 'admin') {
                    $('.input-pin-wrap').removeClass('d-none')
                } else {
                    $('.input-pin-wrap').addClass('d-none')
                }
            })

            $('input,textarea,select').on('keydown', (e) => {
                if(e.key === 'Enter') {
                    e.preventDefault()
                    const nextInput = $(e.target).parents('.form-group').next('.form-group:not(.d-none)').find('input,textarea,select')
                    console.log(nextInput)
                    if(nextInput.length > 0) {
                        nextInput.focus()
                    } else {
                        $('[type="submit"]').focus()
                    }
                }
            })

            $('#username, #name').css({
                textTransform: 'uppercase'
            })
        })
    </script>
@endsection


