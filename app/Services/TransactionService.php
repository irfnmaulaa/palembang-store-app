<?php

namespace App\Services;

use App\Exports\PendingTransactionsExport;
use App\Exports\ProductsExport;
use App\Models\TransactionProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TransactionService
{
    public function export_pending($type, $printed_by = null, $stream = false)
    {
        // define printed by
        if (!$printed_by) {
            $printed_by = auth()->user();
        }

        // define pending transaction filename
        $filename = 'TRANSAKSI PENDING_' . date('YmdHis');

        $start = date('Y-m-d ') . '00:00:01';
        $end   = date('Y-m-d ') . '23:59:59';

        $date_range = request()->query('date_range');
        if ($date_range) {
            $date_range = explode(' - ', $date_range);
            $start = $date_range[0] . ' 00:00:01';
            $end   = $date_range[1] . ' 23:59:59';
        }

        $transaction_products = TransactionProduct::query()
            ->select([
                'transaction_products.*',
                'transactions.date as transaction_date',
                'transactions.code as transaction_code',
                'transactions.type as transaction_type',
                'products.name as product_name',
                'products.variant as product_variant',
                'products.code as product_code',
                'products.unit as product_unit',
                'users.name as creator_name',
            ])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->join('users', 'transactions.created_by', '=', 'users.id')
            ->where('transaction_products.is_verified', 0)
            ->whereBetween('transactions.date', [$start, $end])
            ->orderBy('transactions.date')
            ->orderBy('transactions.created_at')
            ->get();

        switch ($type) {
            case 'excel':
                return (new PendingTransactionsExport($printed_by, $transaction_products))->download($filename . '.xlsx');
            case 'csv':
                return (new PendingTransactionsExport($printed_by, $transaction_products))->download($filename . '.csv', null, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                if ($stream) {
                    return Pdf::loadView('admin.transactions.export.pending-transactions', ['transaction_products' => $transaction_products, 'printed_by' => $printed_by,])->setPaper('a4', 'landscape')->stream($filename . '.pdf');
                }
                return Pdf::loadView('admin.transactions.export.pending-transactions', ['transaction_products' => $transaction_products, 'printed_by' => $printed_by,])->setPaper('a4', 'landscape')->download($filename . '.pdf');
            default:
                return "url export salah";
        }
    }
}
