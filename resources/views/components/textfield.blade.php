<div class="form-group">
    <label for="{{$name}}">{{$label}}</label>
    <input type="{{$type}}" id="{{$name}}" name="{{$name}}" class="form-control form-control-lg {{$errors->first($name) ? 'border-danger' : ''}}" value="{{@$item ? $item[$name] : old($name)}}" {{@$autofocus ? 'autofocus' : ''}}>
    @if($errors->first($name))
        <small class="text-danger mb-0">{{$errors->first($name)}}</small>
    @endif
</div>
