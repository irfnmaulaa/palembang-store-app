<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d');

        if ($request->has('date_range')) {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0];
            $end = $explode[1];
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
        if ($request->has('keyword')) {
            $transactions = $transactions
                ->where(function ($query) use ($request) {
                    $query->where('products.name', 'LIKE', '%' . $request->get('keyword') . '%')
                        ->orWhere('products.code', 'LIKE', '%' . $request->get('keyword') . '%')
                        ->orWhere('transactions.code', 'LIKE', '%' . $request->get('keyword') . '%');
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
            ->paginate(10)
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
}
