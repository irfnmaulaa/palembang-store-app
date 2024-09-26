@foreach($data as $item)
    <div class="p-3 border rounded w-100">
        <h6 class="mb-2">
            <a href="{{route('admin.products.show', ['product' => $item->product, ])}}" class="text-dark">
                {{$item->product->name}} {{$item->product->variant}}
            </a>
        </h6>
        <table class="table table-striped table-sm mb-0">
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
            @php
            $from_classname = get_table_row_classname($item->from_transaction_product->transaction->type);
            $to_classname = get_table_row_classname($item->to_transaction_product->transaction->type);
            @endphp
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
    </div>

@endforeach
