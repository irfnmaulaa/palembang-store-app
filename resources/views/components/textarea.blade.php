<div class="form-group">
    <label for="{{$name}}">{{$label}}</label>
    <textarea rows="3" id="{{$name}}" name="{{$name}}" class="form-control form-control-lg {{$errors->first($name) ? 'border-danger' : ''}}">{{@$item ? $item[$name] : old($name)}}</textarea>
    @if($errors->first($name))
        <small class="text-danger mb-0">{{$errors->first($name)}}</small>
    @endif
</div>
