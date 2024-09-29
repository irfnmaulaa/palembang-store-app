@if(count($data) > 0)
    <form action="{{ route('admin.app_errors.solve', ['type' => 'redundant']) }}" method="POST">
        @csrf
        <table class="table table-striped table-sm mb-0">
            <thead>
            <tr>
                <th class="text-center" style="width: 50px">
                    <div class="d-flex justify-content-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="check_all" />
                        </div>
                    </div>
                </th>
                <th>Tanggal</th>
                <th>No DO</th>
                <th class="text-center">Quantity</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>Keterangan</th>
                <th class="text-center">ID</th>
                <th class="text-center">Jumlah Redudansi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $i => $item)
                @php
                    $className = get_table_row_classname($item->transaction->type)
                @endphp
                <tr>
                    <td class="text-center">
                        <label for="item-{{$i}}" class="form-check d-flex justify-content-center">
                            <input class="form-check-input transaction-checkbox" type="checkbox" name="rec_ids[]" value="{{ $item->id }}" id="item-{{$i}}" />
                        </label>
                    </td>
                    <td class="{{ $className }}">
                        {{\Carbon\Carbon::parse($item->transaction->date)->format('d/m/Y')}}
                    </td>
                    <td>
                        <a href="{{ route('admin.transactions.show', $item->transaction) }}" class="{{ $className }}">
                            {{$item->transaction->code}}
                        </a>
                    </td>
                    <td class="{{ $className }} text-center">
                        {{$item->quantity}}
                    </td>
                    <td>
                        <a href="{{ route('admin.products.show', $item->product) }}" class="{{ $className }}">
                            {{$item->product->name}} {{$item->product->variant}}
                        </a>
                    </td>
                    <td class="{{ $className }}">
                        {{$item->product->code}}
                    </td>
                    <td class="{{ $className }}">
                        {{$item->note}}
                    </td>
                    <td class="{{ $className }} text-center">
                        {{$item->creator->name}}
                    </td>
                    <td class="{{ $className }} text-center">
                        {{$item->duplicate_count}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if(auth()->user()->role === 'admin')
        <div class="mt-4">
            <button type="submit" class="btn btn-solve btn-outline-danger btn-lg disabled">
                Solve <span id="checked-count" class="ms-1">(0)</span>
            </button>
        </div>
        @endif

    </form>
@else
    Tidak ada error terdeteksi.
@endif
