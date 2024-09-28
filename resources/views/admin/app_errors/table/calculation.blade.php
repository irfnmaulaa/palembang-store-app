@if(count($data) > 0)
    <form class="calculation-form" action="{{ route('admin.app_errors.solve', ['type' => 'calculation']) }}" method="POST">
        @csrf
        @foreach(collect($data->items())->groupBy('product_id') as $product_id => $items)
            <div class="calculation-box p-3 border rounded w-100">
                <div class="d-flex align-items-center mb-2 gap-1">
                    <label for="item-{{$product_id}}" class="form-check d-flex justify-content-center">
                        <input class="form-check-input transaction-checkbox" type="checkbox" name="product_ids[]" value="{{ $product_id }}" id="item-{{$product_id}}" />
                    </label>
                    <a href="{{route('admin.products.show', ['product' => $items[0]->product, ])}}" class="text-dark">
                        {{$items[0]->product->name}} {{$items[0]->product->variant}}
                    </a>
                </div>
                @foreach($items as $i => $item)
                    @php
                        $from_classname = get_table_row_classname($item->from_transaction_product->transaction->type);
                        $to_classname = get_table_row_classname($item->to_transaction_product->transaction->type);
                    @endphp
                    <table class="table table-striped table-sm mt-3">
                        <thead>
                        <tr>
                            <th style="width: 120px;"></th>
                            <th style="width: 250px;">Tanggal</th>
                            <th style="width: 250px;">No DO</th>
                            <th>Keterangan</th>
                            <th style="width: 100px;" class="text-center">M</th>
                            <th style="width: 100px;" class="text-center">K</th>
                            <th style="width: 100px;" class="text-center">S</th>
                            <th style="width: 100px;" class="text-center">ID</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="{{ $from_classname }}"></td>
                            <td class="{{ $from_classname }}">{{\Carbon\Carbon::parse($item->from_transaction_product->transaction->date)->format('d/m/Y')}}</td>
                            <td class="{{ $from_classname }}">{{$item->from_transaction_product->transaction->code}}</td>
                            <td class="{{ $from_classname }}">{{$item->from_transaction_product->note}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->from_transaction_product->transaction->type === 'in' ? $item->from_transaction_product->quantity : 0}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->from_transaction_product->transaction->type === 'out' ? $item->from_transaction_product->quantity : 0}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->from_transaction_product->to_stock}}</td>
                            <td class="{{ $from_classname }} text-center">{{@$item->from_transaction_product->creator->name ?? '-'}}</td>
                        </tr>
                        <tr>
                            <td class="text-center">Error <i class="fas fa-arrow-right ms-1"></i></td>
                            <td class="{{ $from_classname }}">{{\Carbon\Carbon::parse($item->to_transaction_product->transaction->date)->format('d/m/Y')}}</td>
                            <td class="{{ $from_classname }}">{{$item->to_transaction_product->transaction->code}}</td>
                            <td class="{{ $from_classname }}">{{$item->to_transaction_product->note}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->to_transaction_product->transaction->type === 'in' ? $item->to_transaction_product->quantity : 0}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->to_transaction_product->transaction->type === 'out' ? $item->to_transaction_product->quantity : 0}}</td>
                            <td class="{{ $from_classname }} text-center">{{$item->to_transaction_product->to_stock}}</td>
                            <td class="{{ $from_classname }} text-center">{{@$item->to_transaction_product->creator->name ?? '-'}}</td>
                        </tr>
                        </tbody>
                    </table>
                @endforeach

            </div>
        @endforeach

        @if(auth()->user()->role === 'admin')
        <div class="mt-4">
            <button type="submit" class="btn btn-solve btn-outline-danger btn-lg disabled">
                Solve <span id="checked-count" class="ms-1">(0)</span>
            </button>
        </div>
        @endif
    </form>

@else
    No calculation error detected.
@endif
