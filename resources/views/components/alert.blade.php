@if(session('message'))
    <script>
        Swal.fire({
            icon: "success",
            title: "Sukses",
            text: "{{session('message')}}",
        });
    </script>
@elseif($errors->any())
    <div class="alert alert-danger">
        <h5 class="mb-0">Gagal</h5>
    </div>
@endif
