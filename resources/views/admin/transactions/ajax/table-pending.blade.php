@foreach($transactions as $i => $tp)
    @php
        $count = $tp->transaction_products()->where('is_verified', 0)->count() + 1;
        $products = $tp->products()->wherePivot('is_verified', 0)->get();
        $className = get_table_row_classname($tp->type);
    @endphp
    <tr>
        @if(auth()->user()->role === 'admin')
            <td rowspan="{{$count}}" class="text-center {{$className}}">

            </td>
        @endif
        <td rowspan="{{$count}}" class="{{$className}}">
            <label for="tp-{{$tp->id}}" class="d-flex align-items-center">
                {{\Carbon\Carbon::parse($tp->date)->format('d/m/Y')}}
            </label>
        </td>
        <td rowspan="{{$count}}" class="{{$className}}">
            <label for="tp-{{$tp->id}}" class="d-flex align-items-center d-flex gap-2 align-items-center">
                @if(auth()->user()->role === 'admin')
                    <div class="form-check">
                        <input class="form-check-input transaction-checkbox" type="checkbox" value="" id="tp-{{$tp->id}}" />
                    </div>
                @endif
                {{$tp->code}}
            </label>
        </td>
    </tr>
    @foreach($products as $product)
        <tr>
            <td class="{{$className}} text-center">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                    {{$product->pivot->quantity}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex gap-2 align-items-center">
                    @if(auth()->user()->role === 'admin')
                        <div class="form-check">
                            <input data-parent="tp-{{$tp->id}}" class="form-check-input product-checkbox" type="checkbox" name="transaction_product_ids[]" value="{{$product->pivot->id}}" id="product-{{$product->id}}" />
                        </div>
                    @endif
                    {{$product->name}} {{$product->variant}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center">
                    {{$product->code}}
                </label>
            </td>
            <td class="{{$className}} text-center">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-center">
                    {{$product->pivot->to_stock}} {{$product->unit}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                    {{$product->pivot->note}}
                </label>
            </td>
            <td class="{{$className}}">
                <label for="product-{{$product->id}}" class="d-flex align-items-center justify-content-start">
                    @if($tp->creator)
                        {{$tp->creator->name}}
                    @endif
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
