<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin')->except(['index']);
    }

    public function index(Request $request)
    {
        $start = date('Y-m-d') . ' 00:00:00';
        $end   = date('Y-m-d') . ' 23:59:59';

        // get first transaction
        $first_transaction = Transaction::orderBy('date')->orderBy('created_at')->first();
        if ($first_transaction) {
            $start = Carbon::parse($first_transaction->date)->format('Y-m-d') . ' 00:00:00';
        }

        if ($request->query('start_date')) {
            $start = $request->query('start_date')  . ' 00:00:00';
        }

        if ($request->query('end_date')) {
            $end = $request->query('end_date') . ' 23:59:59';
        }

        // define instance
        $transactions = Transaction::query()
            ->select('transactions.*')
            ->distinct(['transactions.id'])
            ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 1)
            ->whereBetween('transactions.date', [$start, $end]);

        // searching settings
        if ($request->query('keyword')) {
            $transactions = $transactions
                ->where(function ($query) use ($request) {
                    // Split the keyword into words
                    $words = explode(' ', $request->query('keyword'));

                    foreach ($words as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            // Use REGEXP to match partial words in name, variant, or the concatenated field
                            $subQuery->where('products.name', 'LIKE', "%{$word}%")
                                ->orWhere('products.variant', 'LIKE', "%{$word}%")
                                ->orWhere('transaction_products.note', 'LIKE', "%{$word}%")
                                ->orWhere('transactions.code', 'LIKE', '%' . $word . '%');
                        });
                    }
                });
        }

        // order-by settings
        $order = ['transactions.date', 'desc'];
        if ($request->has('order')) {
            $order_query = explode('-', $request->get('order'));
            if (count($order_query) >= 2) $order = $order_query;
        }

        if ($order[0] === 'created_at') {
            $order[0] = 'transactions.created_at';
        } elseif ($order[0] === 'code') {
            $order[0] = 'transactions.code';
        } elseif ($order[0] === 'type') {
            $order[0] = 'transactions.type';
        }

        // order-by statements
        $transactions = $transactions->orderBy($order[0], $order[1]);
        if ($order[0] === 'transactions.date') {
            $transactions = $transactions->orderBy('transactions.created_at', $order[1]);
        }

        // final statements
        $transactions = $transactions
            ->paginate(get_per_page_default())
            ->appends($request->query());

        // define order options for front-end
        $order_options = [
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Pertama dibuat', 'order' => 'created_at-asc'],
            ['label' => 'Nomor DO A-Z', 'order' => 'code-asc'],
            ['label' => 'Nomor DO Z-A', 'order' => 'code-desc'],
            ['label' => 'Tipe A-Z', 'order' => 'type-asc'],
            ['label' => 'Tipe Z-A', 'order' => 'type-desc'],
        ];

        // clear start date filter
        if ($first_transaction && Carbon::parse($first_transaction->date)->format('Y-m-d') == Carbon::parse($start)->format('Y-m-d')) {
            $start = null;
        }

        // clear end date filter
        if ($end && Carbon::parse($end)->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
            $end = null;
        }

        // return view
        return view('admin.histories.index', compact('transactions', 'order_options', 'start', 'end'));
    }

    public function export(Request $request, $type)
    {
        $start = date('Y-m-d') . ' 00:00:00';
        $end   = date('Y-m-d') . ' 23:59:59';

        // get first transaction
        $first_transaction = Transaction::orderBy('date')->orderBy('created_at')->first();
        if ($first_transaction) {
            $start = Carbon::parse($first_transaction->date)->format('Y-m-d') . ' 00:00:00';
        }

        if ($request->query('start_date')) {
            $start = $request->query('start_date')  . ' 00:00:00';
        }

        if ($request->query('end_date')) {
            $end = $request->query('end_date') . ' 23:59:59';
        }

        $filename = 'TRANSAKSI PERIODE ' . Carbon::parse($start)->format('d-m-Y') . ' SD ' . Carbon::parse($end)->format('d-m-Y') . '_' . Carbon::now()->format('YmdHis');

        // define date range field
        $date_range_field = 'transactions.date';
        if ($request->has('is_verified_transaction')) {
            $date_range_field  = 'transactions.created_at';

            $filename = 'TRANSAKSI TERVERIFIKASI PERIODE ' . Carbon::parse($start)->format('d-m-Y') . ' SD ' . Carbon::parse($end)->format('d-m-Y') . '_' . Carbon::now()->format('YmdHis');
        }

        $transaction_products = TransactionProduct::query()
            ->select([
                'transaction_products.*',
                'transactions.date as transaction_date',
                'transactions.code as transaction_code',
                'transactions.type as transaction_type',
                'products.unit as product_unit',
                'products.name as product_name',
                'products.variant as product_variant',
                'products.code as product_code',
                'users.name as creator_name',
            ])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->join('users', 'transactions.created_by', '=', 'users.id')
            ->where('transaction_products.is_verified', 1)
            ->whereBetween($date_range_field, [$start, $end])
            ->orderBy(DB::raw('DATE(transactions.date)'))
//            ->orderBy('transactions.id')
            ->orderBy('transaction_products.id')
            ->get();

        switch ($type) {
            case 'excel':
                return (new TransactionsExport($transaction_products))->download($filename . '.xlsx');
            case 'csv':
                return (new TransactionsExport($transaction_products))->download($filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return Pdf::loadView('admin.transactions.export.verified-transactions', ['transaction_products' => $transaction_products])->setPaper('a4')->download($filename . '.pdf');
            default:
                return "url export salah";
        }
    }
}
