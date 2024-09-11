<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\TransactionProduct;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductDetailExport implements FromCollection
{
    use Exportable;

    public function __construct($product, $start, $end)
    {
        $this->product = $product;
        $this->start = $start;
        $this->end = $end;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect([
            ['Tanggal', 'Tipe', 'No DO', 'Keterangan', 'M', 'K', 'S', 'ID']
        ]);

        $transaction_products = TransactionProduct::query()
            ->select(['transaction_products.*', 'transactions.*'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->where('transaction_products.is_verified', 1)
            ->where('transaction_products.product_id', $this->product->id)
            ->whereBetween('transactions.date', [$this->start, $this->end])
            ->orderByDesc('transactions.date')
            ->get();

        return $data->merge($transaction_products->map(function ($tp, $i) {
            $type = $tp->transaction->type === 'in' ? 'Barang Masuk' : 'Barang Keluar';
            $date = Carbon::parse($tp->transaction->date)->format('d/m/Y');
            $code = $tp->transaction->code;

            return [ $date, $type, $code, $tp->note, $tp->transaction->type === 'in' ? $tp->quantity : '0' , $tp->transaction->type === 'out' ? $tp->quantity : '0', $tp->to_stock . '', $tp->creator->name];
        }));
    }
}
