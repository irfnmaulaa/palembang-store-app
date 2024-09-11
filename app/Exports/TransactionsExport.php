<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionProduct;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionsExport implements FromCollection
{
    use Exportable;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect([
            ['Tanggal', 'Tipe', 'No DO', 'Quantity', 'Nama Barang', 'Kode Barang', 'Keterangan', 'Sisa', 'ID',]
        ]);

        $transaction_products = TransactionProduct::query()
            ->select('transaction_products.*')
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 1)
            ->whereBetween('transactions.date', [$this->start, $this->end])
            ->orderByDesc('transactions.date')
            ->get();

        return $data->merge($transaction_products->map(function ($tp, $i) {
            $type = $tp->transaction->type === 'in' ? 'Barang Masuk' : 'Barang Keluar';
            $date = Carbon::parse($tp->transaction->date)->format('d/m/Y');
            $code = $tp->transaction->code;

            $creator = "";
            if ($tp->creator) {
                $creator = $tp->creator->name;
            }

            return [ $date, $type, $code, $tp->quantity, $tp->product->name . ' ' . $tp->product->variant, $tp->product->code, $tp->note, $tp->to_stock, $creator];
        }));
    }
}
