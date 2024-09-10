@foreach($transactions as $i => $tp)
    @php
        $count = $tp->transaction_products()->where('is_verified', 0)->count() + 1;
        $products = $tp->products()->wherePivot('is_verified', 0)->get();
        $className = $tp->type == 'in' ? 'text-primary' : 'text-danger';
    @endphp
    <tr>
        <td rowspan="{{$count}}" class="text-center {{$className}}">
            <label for="tp-{{$tp->id}}" class="form-check d-flex justify-content-center">
                <input class="form-check-input transaction-checkbox" type="checkbox" value="" id="tp-{{$tp->id}}" />
            </label>
        </td>
        <td rowspan="{{$count}}" class="{{$className}}">
            <label for="tp-{{$tp->id}}" class="d-flex align-items-center">
                {{\Carbon\Carbon::parse($tp->date)->format('d/m/Y H.i')}}
            </label>
        </td>
        <td rowspan="{{$count}}" class="{{$className}}">
            <label for="tp-{{$tp->id}}" class="d-flex align-items-center">
                {{$tp->code}}
            </label>
        </td>
        <td rowspan="{{$count}}" class="{{$className}}">
            <label for="tp-{{$tp->id}}" class="d-flex align-items-center">
                @if($tp->creator)
                    {{$tp->creator->name}}
                @else
                    -
                @endif
            </label>
        </td>
    </tr>
    @foreach($products as $product)
        <tr>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex gap-2 align-items-center">
                    <div class="form-check">
                        <input data-parent="tp-{{$tp->id}}" class="form-check-input product-checkbox" type="checkbox" name="transaction_product_ids[]" value="{{$product->pivot->id}}" id="product-{{$product->id}}" />
                    </div>
                    {{$product->name}} / {{$product->variant}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center">
                    {{$product->code}}
                </label>
            </td>
            <td class="{{$className}} text-center">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                    {{$product->pivot->from_stock}}
                </label>
            </td>
            <td class="{{$className}} text-center">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                    {{$product->pivot->quantity}}
                </label>
            </td>
            <td class="{{$className}} text-center">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                    {{$product->pivot->to_stock}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                    {{$product->unit}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                    {{$product->pivot->note}}
                </label>
            </td>
        </tr>
    @endforeach
@endforeach
@if(count($transactions) == 0)
    <tr>
        <td colspan="11" class="text-center">Tidak ada data</td>
    </tr>
@endif
