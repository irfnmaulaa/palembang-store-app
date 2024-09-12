@php
    $row_styles = "border: 1px solid #111111";
@endphp

<table class="table">
    <tbody>
    @foreach ($products as $product)
        <tr>
            <td style="{{ $row_styles }}">
                {{ $product->name }} {{ $product->variant }}
            </td>
            <td style="{{ $row_styles }}">{{ $product->code }}</td>
            <td style="{{ $row_styles }}"></td>
            <td style="{{ $row_styles }}">{{ $product->unit }}</td>
            <td style="{{ $row_styles }}">{{ $product->stock }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
