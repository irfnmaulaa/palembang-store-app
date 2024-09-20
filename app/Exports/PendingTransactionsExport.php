<?php

namespace App\Exports;

use App\Models\TransactionProduct;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PendingTransactionsExport implements FromView, WithEvents
{
    use Exportable;

    public function view() : View
    {
        $start = date('Y-m-d ') . '00:00:01';
        $end = date('Y-m-d ') . '23:59:59';

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
            ->orderByDesc('transactions.date')
            ->get();

        return view('admin.transactions.export.pending-transactions-pdf', compact('transaction_products'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }
}
