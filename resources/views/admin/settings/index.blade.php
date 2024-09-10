@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column gap-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column gap-1">
                <h2 class="mb-0">Pengaturan</h2>
                <div class="d-flex align-items-center gap-2 fs-6 text-muted">
                    <a href="{{route('admin.dashboard')}}">Beranda</a> <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
                    Pengaturan
                </div>
            </div>
        </div>

        <x-alert></x-alert>

        <form action="" method="POST">
            @csrf
            <div class="card border shadow-none">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-end">
                            <label for="working_start">Jam Mulai Aktifitas</label>
                        </div>
                        <div class="col-md-10">
                            <input type="time" step="1" class="form-control form-control-lg" name="working_start" id="working_start" value="{{$working_start}}">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-2 text-end">
                            <label for="working_end" class="mb-4">Jam Selesai Aktifitas</label>
                        </div>
                        <div class="col-md-10">
                            <input type="time" step="1" class="form-control form-control-lg" name="working_end" id="working_end" value="{{$working_end}}">
                            <small class="text-muted">
                                User dengan hak akses <b>admin gudang</b> dan <b>admin toko</b> tidak dapat melakukan aktifitas diluar waktu bekerja.
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 text-end">
                            Urutan Menu
                        </div>
                        <div class="col-md-10">
                            <div class="card shadow-none border">
                                <div class="card-body d-flex flex-column gap-3" id="sortable">
                                    @foreach($menus as $menu)
                                        <div class="card shadow-none border card-item">
                                            <div class="card-body p-0 d-flex align-items-center gap-3">
                                                <div class="py-2 px-3" style="border-right: 1px solid #ddd;"><i class="fas fa-bars"></i></div> <p class="mb-0">{{$menu->label}}</p>
                                                <input type="hidden" name="menus[]" value="{{json_encode($menu)}}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-2"></div>
                        <div class="col-md-10">
                            <button type="submit" class="btn btn-primary btn-lg mt-3">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


@section('js')
    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
    <script>
        $( function() {
            $( "#sortable" ).sortable();
        } );
    </script>
@endsection
