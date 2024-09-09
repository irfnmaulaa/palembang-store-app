<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('cutoff')->only(['verify', 'create']);
    }

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
        $transactions_pending = Transaction::query()
            ->select('transactions.*')
            ->distinct()
            ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 0)
            ->whereBetween('transactions.date', [$start, $end]);

        // searching settings
        if ($request->has('keyword')) {
            $transactions_pending = $transactions_pending
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
        $transactions_pending = $transactions_pending->orderBy($order[0], $order[1]);

        // final statements
        $transactions_pending = $transactions_pending
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

        // define instance
        $transactions_verified = Transaction::query()
            ->select('transactions.*')
            ->distinct()
            ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 1)
            ->whereBetween('transactions.date', [$start, $end])
            ->orderByDesc('transactions.date');

        // searching settings
        if ($request->has('keyword2')) {
            $transactions_verified = $transactions_verified
                ->where(function ($query) use ($request) {
                    $query->where('products.name', 'LIKE', '%' . $request->get('keyword2') . '%')
                        ->orWhere('products.code', 'LIKE', '%' . $request->get('keyword2') . '%')
                        ->orWhere('transactions.code', 'LIKE', '%' . $request->get('keyword2') . '%');
                });

        }

        // order-by settings
        $order = ['transactions.created_at', 'desc'];
        if ($request->has('order2')) {
            $order_query = explode('-', $request->get('order2'));
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
        $transactions_verified = $transactions_verified->orderBy($order[0], $order[1]);

        // final statements
        $transactions_verified = $transactions_verified
            ->paginate(5, ['*'], 'page2')
            ->appends($request->query());

        // return view
        return view('admin.transactions.index', compact('transactions_pending', 'transactions_verified', 'order_options', 'start', 'end'));
    }

    public function create()
    {
        $item = [
            'date' => date('Y-m-d'),
            'code' => ''
        ];
        $action = request('type') === 'in' ? 'Barang Masuk' : 'Barang Keluar';
        return view('admin.transactions.form', compact('item', 'action'));
    }

    public function store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'code' => ['required', 'unique:transactions,code'],
            'date' => ['required', 'date_format:Y-m-d'],
            'products' => ['required', 'array'],
            'products.*' => ['required'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:1'],
            'products.*.note' => ['required'],
            'type' => ['required', 'in:in,out'],
        ]);

        // create transaction
        $transaction = $request->user()->transactions_created()->create([
            'code' => $validated['code'],
            'date' => $validated['date'],
            'type' => $validated['type'],
        ]);

        // create transaction products
        foreach ($validated['products'] as $p) {
            $product = Product::find($p['product_id']);
            $last_product_transaction = $product->transaction_products()->where('is_verified', 1)->orderByDesc('id')->first();
            $last_product_stock = $last_product_transaction ? $last_product_transaction->to_stock : 0;

            $from_stock = $last_product_stock;
            $to_stock = $last_product_stock + $p['quantity'];
            if ($validated['type'] === 'out') {
                $to_stock = $last_product_stock - $p['quantity'];
            }

            $transaction->transaction_products()->create([
                'product_id' => $p['product_id'],
               'quantity' => $p['quantity'],
               'from_stock' => $from_stock,
               'to_stock' => $to_stock,
               'note' => $p['note'],
                'created_by' => auth()->user()->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan',
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'transaction_product_ids' => ['required', 'array'],
            'transaction_product_ids.*' => ['required', 'exists:transaction_products,id'],
        ]);

        $is_deleted = $request->has('delete');
        foreach ($validated['transaction_product_ids'] as $transaction_product_id) {
            $transaction_product = TransactionProduct::where('id', $transaction_product_id)->where('is_verified', 0)->first();
            if (!$transaction_product) {
                return redirect()->back()->withErrors(['transaction_product_id' => 'Transaksi tidak dapat diproses']);
            }

            if ($is_deleted) {
                $transaction_product->delete();
            } else {
                $transaction_product->update([
                    'is_verified' => 1,
                    'verified_by' => auth()->user()->id,
                ]);
            }
        }

        return redirect()->back()->with('message', 'Transaksi berhasil ' . ($is_deleted ? 'dihapus' : 'diverifikasi'));
    }
}
