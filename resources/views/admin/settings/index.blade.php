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

        <form action="" method="POST">
            @csrf
            <div class="card border shadow-none">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-end">
                            <label for="working_start">Jam Mulai Aktifitas</label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control form-control-lg" name="working_start" id="working_start" value="{{$working_start}}">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-2 text-end">
                            <label for="working_end" class="mb-4">Jam Selesai Aktifitas</label>
                        </div>
                        <div class="col-md-10">
                            <input type="text" class="form-control form-control-lg" name="working_end" id="working_end" value="{{$working_end}}">
                            <small class="text-muted">
                                User dengan hak akses <b>admin gudang</b> dan <b>admin toko</b> tidak dapat melakukan aktifitas diluar waktu bekerja.
                            </small>
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
    <script>
        $('.time-picker').daterangepicker({
            timePicker: true,
            timePicker24Hour: true,
            timePickerIncrement: 1,
            timePickerSeconds: true,
            locale: {
                format: 'HH:mm:ss'
            }
        }).on('show.daterangepicker', function (ev, picker) {
            picker.container.find(".calendar-table").hide();
        });
    </script>
@endsection
