<?php

namespace App\Http\Controllers;

use App\Exports\ProductDetailExport;
use App\Exports\TransactionsExport;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use App\Models\TransactionProduct;
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
        $start = date('Y')  . '-01-01 00:00:00';
        $end = date('Y-m-d')  . ' 23:59:59';

        // get first transaction
        /*
        $first_transaction = Transaction::orderBy('created_at')->first();
        if ($first_transaction) {
            $start = $first_transaction->created_at->format('Y-m-d H:i:s');
        }
        */

        if ($request->has('date_range')) {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0]  . ' 00:00:00';
            $end = $explode[1] . ' 23:59:59';
        }

        // define instance
        $transactions = Transaction::query()
            ->select('transactions.*')
            ->distinct()
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
        $order = ['transactions.created_at', 'desc'];
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

        // return view
        return view('admin.histories.index', compact('transactions', 'order_options', 'start', 'end'));
    }

    public function export(Request $request, $type)
    {
        if ($request->has('date_range') && $request->query('date_range') != '') {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0] . ' 00:00:00';
            $end = $explode[1] . ' 23:59:59';
        } else {
            $start = date('Y')  . '-01-01 00:00:00';

            // get first transaction
            /*
            $first_transaction = Transaction::orderBy('created_at')->first();
            if ($first_transaction) {
                $start = $first_transaction->created_at->format('Y-m-d H:i:s');
            }
            */

            $end = date('Y-m-d') . ' 23:59:59';
        }

        $filename = 'TRANSAKSI PERIODE ' . Carbon::parse($start)->format('d-m-Y') . ' SD ' . Carbon::parse($end)->format('d-m-Y') . '_' . Carbon::now()->format('YmdHis');

        switch ($type) {
            case 'excel':
                return (new TransactionsExport($start, $end))->download($filename . '.xlsx');
            case 'csv':
                return (new TransactionsExport($start, $end))->download($filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return (new TransactionsExport($start, $end))->download($filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return "url export salah";
        }
    }
}
