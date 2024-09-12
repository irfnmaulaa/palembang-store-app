<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class TransactionExport implements FromView
{
    use Exportable;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function view() : View
    {
        return view('admin.transactions.export.transaction-pdf', [
            'transaction' => $this->transaction,
        ]);
    }
}
