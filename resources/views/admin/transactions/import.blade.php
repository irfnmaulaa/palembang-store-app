@foreach($errors->all() as $error)
    <div>{{$error}}</div>
@endforeach
<form action="" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" id="file">
    <input type="submit" value="submit">
</form>
