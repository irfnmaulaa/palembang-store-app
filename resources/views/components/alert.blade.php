@if(session('success'))
    <div class="alert alert-success">
        {{session('success')}}
    </div>
@elseif($errors->any())
    <div class="alert alert-danger">
        <h5>Invalid field</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>
                    {{$error}}
                </li>
            @endforeach
        </ul>
    </div>
@endif
