<?php

namespace App\Http\Controllers;

use App\Exports\PendingTransactionsExport;
use App\Exports\TransactionExport;
use App\Imports\TransactionsImport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->middleware('cutoff')->only(['verify']);
        $this->middleware('super_admin')->except(['index', 'create', 'store', 'export_per_transaction', 'export_pending']);

        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {

        // define instance
        $transactions_pending = Transaction::query()
            ->select('transactions.*')
            ->distinct()
            ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 0);

        // searching settings
        if ($request->has('keyword')) {
            $transactions_pending = $transactions_pending
                ->where(function ($query) use ($request) {
                    // Split the keyword into words
                    $words = explode(' ', $request->query('keyword'));

                    foreach ($words as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            // Use REGEXP to match partial words in name, variant, or the concatenated field
                            $subQuery->where('products.name', 'LIKE', "%{$word}%")
                                ->orWhere('products.variant', 'LIKE', "%{$word}%")
                                ->orWhere('products.code', 'LIKE', "%{$word}%")
                                ->orWhere('transactions.code', 'LIKE', '%' . $word . '%');
                        });
                    }
                });

        }

        // order-by settings
        $order = ['transactions.date', 'asc'];
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
        if ($order[0] === 'transactions.date') {
            $transactions_pending = $transactions_pending->orderBy('transactions.created_at', $order[1]);
        }

        // final statements
        $transactions_pending = $transactions_pending
            ->paginate( get_per_page_default() * 2 )
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

        // define start and date for verified transactions
        $start = date('Y-m-d') . ' 00:00:00';
        $end = date('Y-m-d') . ' 23:59:59';

        // define instance
        $transactions_verified = Transaction::query()
            ->select('transactions.*')
            ->distinct()
            ->join('transaction_products', 'transactions.id', '=', 'transaction_products.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_products.product_id')
            ->where('transaction_products.is_verified', 1)
            ->whereBetween('transactions.created_at', [$start, $end]);
            // ->orderByDesc('transactions.date');

        // searching settings
        if ($request->has('keyword2')) {
            $transactions_verified = $transactions_verified
                ->where(function ($query) use ($request) {
                    // Split the keyword into words
                    $words = explode(' ', $request->query('keyword2'));

                    foreach ($words as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            // Use REGEXP to match partial words in name, variant, or the concatenated field
                            $subQuery->where('products.name', 'LIKE', "%{$word}%")
                                ->orWhere('products.variant', 'LIKE', "%{$word}%")
                                ->orWhere('products.code', 'LIKE', "%{$word}%")
                                ->orWhere('transactions.code', 'LIKE', '%' . $word . '%');
                        });
                    }
                });

        }

        // order-by settings
        $order = ['transactions.date', 'asc'];
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
        if ($order[0] === 'transactions.date') {
            $transactions_verified = $transactions_verified->orderBy('transactions.created_at', $order[1]);
        }

        // final statements
        $transactions_verified = $transactions_verified
            ->paginate(get_per_page_default(), ['*'], 'page2')
            ->appends($request->query());

        if ($request->ajax()) {
            // return json
            return response()->json([
                'transactions_pending' => [
                    'table' => view('admin.transactions.ajax.table-pending', ['transactions' => $transactions_pending])->render(),
                    'pagination' => view('admin.transactions.ajax.pagination', ['transactions' => $transactions_pending])->render(),
                    'summary' => view('admin.transactions.ajax.summary', ['transactions' => $transactions_pending])->render(),
                ],
                'transactions_verified' => [
                    'table' => view('admin.transactions.ajax.table', ['transactions' => $transactions_verified])->render(),
                    'pagination' => view('admin.transactions.ajax.pagination', ['transactions' => $transactions_verified])->render(),
                    'summary' => view('admin.transactions.ajax.summary', ['transactions' => $transactions_verified])->render(),
                ],
            ]);
        } else {
            // return view
            return view('admin.transactions.index', compact('transactions_pending', 'transactions_verified', 'order_options', 'start', 'end'));
        }
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
            'code' => ['required'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'products' => ['required', 'array'],
            'products.*' => ['required'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'numeric', 'min:1'],
            'products.*.note' => ['required'],
            'type' => ['required', 'in:in,out'],
            'with_print' => ['nullable', 'boolean'],
        ]);

        // default now for date
        if (!$validated['date']) {
            $validated['date'] = date('Y-m-d H:i:s');
        } else {
            $validated['date'] .= ' ' . date('H:i:s');
        }

        // create transaction
        $transaction = $request->user()->transactions_created()->create([
            'code' => $validated['code'],
            'date' => $validated['date'],
            'type' => $validated['type'],
        ]);

        // create transaction products
        foreach ($validated['products'] as $p) {
            $product = Product::find($p['product_id']);

            // update product code
            if (!empty($p['product_code']) && $validated['type'] === 'in' && $product->code != $p['product_code']) {
                $product->update([
                    'code' => $p['product_code'],
                ]);
            }

            $last_product_transaction = $product->transaction_products()
                ->join('transactions', 'transactions.id', '=', 'transaction_products.transaction_id');

            $date = Carbon::parse($validated['date'])->format('Y-m-d');
            if ($date != date('Y-m-d')) {
                $last_product_transaction = $last_product_transaction->whereDate('transactions.date', '<=', $date);
            }

            $last_product_transaction = $last_product_transaction
                ->where('transaction_products.is_verified', 1)
                ->orWhere(function ($query) use ($transaction, $product) {
                    $query
                        ->where('transaction_products.is_verified', 0)
                        ->whereDate('transactions.date', Carbon::parse($transaction->date)->format('Y-m-d'))
                        ->where('transaction_products.product_id', $product->id);
                })
                ->orderByDesc(DB::raw('DATE(transactions.date)'))
                ->orderByDesc('transaction_products.id')
                ->first();

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

        try {
            // broadcast to other user for transaction update
            broadcast(new \App\Events\RefreshPageEvent(auth()->user()->name . ' baru saja menambahkan transaksi barang ' . ($validated['type'] === 'in' ? 'masuk' : 'keluar')  . '.'));
        } catch (\Exception $exception) {

        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan',
            'redirect_url' => $validated['with_print'] ? route('admin.transactions.index', ['with_print' => 1, 'transaction_id' => $transaction->id]) : route('admin.transactions.index')
        ]);
    }

    public function show(Request $request, Transaction $transaction)
    {
        return view('admin.transactions.show', compact('transaction'));
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'transaction_product_ids' => ['required', 'array'],
            'transaction_product_ids.*' => ['required', 'exists:transaction_products,id'],
            'pin' => ['nullable'],
        ]);

        // check pin
        if (empty($validated['pin']) || !Hash::check($validated['pin'], $request->user()->pin)) {
            return redirect()->back()->with('messageError', 'Pin tidak valid');
        }

        $is_deleted = $request->has('delete');
        foreach ($validated['transaction_product_ids'] as $transaction_product_id) {
            $transaction_product = TransactionProduct::where('id', $transaction_product_id)->where('is_verified', 0)->first();
            if (!$transaction_product) {
                return redirect()->back()->withErrors(['transaction_product_id' => 'Transaksi tidak dapat diproses']);
            }

            if ($is_deleted) {
                $transaction_id = $transaction_product->transaction_id;
                $transaction_product->delete();

                // delete transaction if doesn't have any transaction product
                if (TransactionProduct::where('transaction_id', $transaction_id)->count() <= 0) {
                    Transaction::destroy($transaction_id);
                }
            } else {
                $transaction_product->update([
                    'is_verified' => 1,
                    'verified_by' => auth()->user()->id,
                ]);
            }
        }

        try {
            // broadcast to other user for transaction update
            broadcast(new \App\Events\RefreshPageEvent(auth()->user()->name . ' baru saja ' . ($is_deleted ? 'menghapus' : 'memverifikasi') . ' data transaksi pending.'));
        } catch (\Exception $exception) {

        }

        return redirect()->back()->with('message', 'Transaksi berhasil ' . ($is_deleted ? 'dihapus' : 'diverifikasi'));
    }

    public function export_pending($type)
    {

        return $this->transactionService->export_pending($type);
    }

    public function export_per_transaction(Transaction $transaction)
    {
        $filename = 'TRANSAKSI_' . str_replace('/', '-', $transaction->code) . '_' . date('YmdHis') . '.pdf';

        return Pdf::loadView('admin.transactions.export.transaction-pdf', ['transaction' => $transaction])->setPaper('A6', 'landscape')->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'dpi' => 150,
        ])->download($filename);
    }
}
