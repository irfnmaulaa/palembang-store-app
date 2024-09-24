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
        $start = date('Y-m-d') . ' 00:00:00';
        $end = date('Y-m-d') . ' 23:59:59';

        if ($request->has('date_range')) {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0] . ' 00:00:00';
            $end = $explode[1]  . ' 23:59:59';
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
            ->whereBetween('transactions.date', [$start, $end]);
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

        // broadcast to other user for transaction update
        broadcast(new \App\Events\RefreshPageEvent(auth()->user()->name . ' baru saja ' . ($is_deleted ? 'menghapus' : 'memverifikasi') . ' data transaksi pending.'));

        return redirect()->back()->with('message', 'Transaksi berhasil ' . ($is_deleted ? 'dihapus' : 'diverifikasi'));
    }

    public function export_pending($type)
    {

        return $this->transactionService->export_pending($type);
    }

    public function export_per_transaction(Transaction $transaction)
    {
        $filename = 'TRANSAKSI_' . str_replace('/', '-', $transaction->code) . '_' . date('YmdHis') . '.pdf';

        return Pdf::loadView('admin.transactions.export.transaction-pdf', ['transaction' => $transaction])->setPaper('A6', 'landscape')->download($filename);
    }

    public function import(Request $request)
    {
        $items = [
            ["date" => "45532", "transaction_code" => "S 2585", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "1183 HARYADI", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2585", "product_name" => "PAPAN GIPSUM KNAUF", "quantity" => "25", "product_unit" => "LBR", "note" => "1183 HARYADI", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2585", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "20", "product_unit" => "BTG", "note" => "1183 HARYADI", "to_stock" => "118", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2585", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "40", "product_unit" => "BTG", "note" => "1183 HARYADI", "to_stock" => "67", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2586", "product_name" => "MULTIPLEX 15 MM F", "quantity" => "1", "product_unit" => "LBR", "note" => "1196 PEREZA", "to_stock" => "20", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2586", "product_name" => "MULTIPLEX 9 MM", "quantity" => "1", "product_unit" => "LBR", "note" => "1196 PEREZA", "to_stock" => "15", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2586", "product_name" => "GEROBAK PASIR DASH GLUCK HIJAU", "quantity" => "1", "product_unit" => "BH", "note" => "1198 JUAL", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2586", "product_name" => "SEMEN TIGA RODA", "quantity" => "20", "product_unit" => "ZAK", "note" => "1187 EUIS", "to_stock" => "211", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2587", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "30", "product_unit" => "ZAK", "note" => "1257 PT. TRIMITRA", "to_stock" => "212", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2587", "product_name" => "P PVC POLOS ABU-ABU", "quantity" => "1", "product_unit" => "BH", "note" => "1201 JUAL", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2587", "product_name" => "SOCKET RCAW 3'' AW", "quantity" => "2", "product_unit" => "BH", "note" => "1203 JUAL", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2587", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "1", "product_unit" => "BTG", "note" => "1204 SINAR TERANG", "to_stock" => "117", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2587", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "16", "product_unit" => "BTG", "note" => "1199 PT. TRIMMITRA", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2588", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "40", "product_unit" => "BTG", "note" => "1200 H. AANG", "to_stock" => "69", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2588", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "40", "product_unit" => "BTG", "note" => "1200 H. AANG", "to_stock" => "105", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2588", "product_name" => "CASTING A+", "quantity" => "2", "product_unit" => "SAK", "note" => "1208 JUAL", "to_stock" => "109", "transaction_type" => "KELUAR"],
            ["date" => "45532", "transaction_code" => "S 2588", "product_name" => "ARWANA 40 / 40 DALLAS GREY", "quantity" => "25", "product_unit" => "DUS", "note" => "1213 SINAR TERANG", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "M 2589", "product_name" => "KEMTONE INT 1 KG DBC", "quantity" => "24", "product_unit" => "KLG", "note" => "MASUK", "to_stock" => "26", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2589", "product_name" => "KEMTONE EXT 5 KG TBM", "quantity" => "12", "product_unit" => "GLN", "note" => "MASUK", "to_stock" => "20", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2589", "product_name" => "COLORTONE 25 KG ALK", "quantity" => "4", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2589", "product_name" => "SPECTRUM 25 KG TBA", "quantity" => "10", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "27", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2589", "product_name" => "SPECTRUM WP 25 KG WHITE", "quantity" => "15", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "15", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "B 0120", "product_name" => "FLEXSEAL FS WALL", "quantity" => "3", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "45", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "B 0120", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "43", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "B 0120", "product_name" => "BATA HEBEL 10 X 20 X 60", "quantity" => "200", "product_unit" => "BH", "note" => "BANGUNAN OTISTA", "to_stock" => "224", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "T 2590", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "2", "product_unit" => "BTG", "note" => "MASUK D/ BANGUNAN", "to_stock" => "107", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2591", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "50", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "93", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "ASIA 40 / 40 CORSO BLACK", "quantity" => "2", "product_unit" => "DUS", "note" => "1222 JUAL", "to_stock" => "20", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "ASIA 40 / 40 OSCAR GREY", "quantity" => "2", "product_unit" => "DUS", "note" => "1222 JUAL", "to_stock" => "23", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "PARALON LISTRIK CONDUIT 20MM", "quantity" => "5", "product_unit" => "BTG", "note" => "1223 JUAL", "to_stock" => "88", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "MULTIPLEX 8 MM", "quantity" => "2", "product_unit" => "LBR", "note" => "1238 MANG UJANG", "to_stock" => "34", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "TABUNG POMPA ORANGE + SOK", "quantity" => "1", "product_unit" => "BH", "note" => "1234 JUAL", "to_stock" => "37", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "RAM KOTAK 1CM HIJAU", "quantity" => "1", "product_unit" => "ROLL", "note" => "1240 YAYA AGRO", "to_stock" => "30", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2592", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "20", "product_unit" => "SAK", "note" => "1241 YUNUS", "to_stock" => "141", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2593", "product_name" => "KNEE POLOS RCAW 1'' AW", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2593", "product_name" => "PURATEX PUTIH / 25 KG", "quantity" => "3", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2593", "product_name" => "KAWAT BETON 25 KG", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "KEMTONE EXT 25 KG TBM", "quantity" => "2", "product_unit" => "PIL", "note" => "1251 PAK AA", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "KEMTONE INT 25 KG UWI", "quantity" => "3", "product_unit" => "PIL", "note" => "1251 PAK AA", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "PREMIERE 25 / 25 25570 COKLAT", "quantity" => "4", "product_unit" => "DUS", "note" => "1254 JUAL", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "1257 CI YANTI", "to_stock" => "41", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "1255 PAK AA", "to_stock" => "210", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "WH 20 / 25 CREAM", "quantity" => "1", "product_unit" => "DUS", "note" => "1255 PAK AA", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2594", "product_name" => "ASIA 30 / 30 OSCAR BLACK", "quantity" => "10", "product_unit" => "DUS", "note" => "1255 PAK AA", "to_stock" => "79", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "GRC PINK BOARD", "quantity" => "1", "product_unit" => "LBR", "note" => "1250 H. ANDRI", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "2", "product_unit" => "BTG", "note" => "1250 H. ANDRI", "to_stock" => "28", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "1261 JUAL", "to_stock" => "140", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "PAPAN GIPSUM A+", "quantity" => "1", "product_unit" => "LBR", "note" => "1260 LUCKY", "to_stock" => "120", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "2", "product_unit" => "BTG", "note" => "1260 LUCKY", "to_stock" => "65", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "CLOSET TOTO PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => "1264 JUAL", "to_stock" => "13", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "1264 JUAL", "to_stock" => "215", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2595", "product_name" => "WH 20 / 25 PINK", "quantity" => "1", "product_unit" => "DUS", "note" => "1279 JUAL", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "M 2596", "product_name" => "PAPAN GIPSUM KNAUF", "quantity" => "120", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "132", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2596", "product_name" => "MU 380 / 40 KG", "quantity" => "30", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "66", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "M 2596", "product_name" => "MU 480 / 25 KG", "quantity" => "14", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "16", "transaction_type" => "MASUK"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "PAPAN GIPSUM A+", "quantity" => "25", "product_unit" => "LBR", "note" => "1258 PAK DODO", "to_stock" => "95", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "MU 200 / 20 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "1258 PAK DODO", "to_stock" => "28", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "MU 380 / 40 KG", "quantity" => "1", "product_unit" => "SAK", "note" => "1258 PAK DODO", "to_stock" => "65", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "1258 PAK DODO", "to_stock" => "40", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "BESI 10 MSI", "quantity" => "10", "product_unit" => "BTG", "note" => "1271 KOMINFO", "to_stock" => "355", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "BESI 8 MSTI", "quantity" => "4", "product_unit" => "BTG", "note" => "1271 KOMINFO", "to_stock" => "139", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "COMPONE A+", "quantity" => "3", "product_unit" => "SAK", "note" => "1271 KOMINFO", "to_stock" => "37", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "KACA WASTAFEL 50/70", "quantity" => "1", "product_unit" => "BH", "note" => "1277 JUAL", "to_stock" => "7", "transaction_type" => "KELUAR"],
            ["date" => "45533", "transaction_code" => "S 2597", "product_name" => "LISPLANG ROYAL MOTIF KAYU 30 CM / 405", "quantity" => "2", "product_unit" => "LBR", "note" => "1276 H. RUDI", "to_stock" => "67", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "M 2598", "product_name" => "BATA HEBEL 10 X 20 X 60", "quantity" => "1042", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "1266", "transaction_type" => "MASUK"],
            ["date" => "45534", "transaction_code" => "T 2599", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "10", "product_unit" => "SAK", "note" => "DODI ANDRIAN", "to_stock" => "130", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "T 2599", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "DODI ANDRIAN", "to_stock" => "200", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "T 2599", "product_name" => "WAVIN AW 4'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "DODI ANDRIAN", "to_stock" => "64", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "T 2599", "product_name" => "WAVIN AW 1'' AW", "quantity" => "70", "product_unit" => "BTG", "note" => "ARKAN", "to_stock" => "71", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2600", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "DISPLAY", "to_stock" => "35", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "BCP MINIMALIS CHROME JKM 52", "quantity" => "1", "product_unit" => "bh", "note" => "1285 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "70", "product_unit" => "ZAK", "note" => "1257 PT TRIMITRA", "to_stock" => "142", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "1281 H. AANG", "to_stock" => "190", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "WAVIN AW 2'' AW", "quantity" => "4", "product_unit" => "BTG", "note" => "1291 LASKA HOTEL", "to_stock" => "66", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "SEMEN HEBEL UNIMIX 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "1310 JUAL", "to_stock" => "14", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "1", "product_unit" => "BTG", "note" => "1307 AGUS", "to_stock" => "64", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "SEMEN TIGA RODA", "quantity" => "2", "product_unit" => "ZAK", "note" => "1307 AGUS", "to_stock" => "188", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "PIPA GI MED B 1 1/2\" B", "quantity" => "1", "product_unit" => "BTG", "note" => "1317 RS. HAMORI", "to_stock" => "9", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "HELM PROYEK PUTIH", "quantity" => "6", "product_unit" => "BH", "note" => "1317 RS. HAMORI", "to_stock" => "108", "transaction_type" => "KELUAR"],
            ["date" => "45534", "transaction_code" => "S 2601", "product_name" => "WASTAFEL VOLK PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => "1317 RS. HAMORI", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "M 2602", "product_name" => "T STAINLESS 500 L", "quantity" => "5", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "5", "transaction_type" => "MASUK"],
            ["date" => "45535", "transaction_code" => "T 2603", "product_name" => "PLASTIK COR -", "quantity" => "1", "product_unit" => "BALL", "note" => "MASUK D/ BANGUNAN", "to_stock" => "83", "transaction_type" => "MASUK"],
            ["date" => "45535", "transaction_code" => "B 0121", "product_name" => "FLEXSEAL FS WALL", "quantity" => "2", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "43", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "1323 HARYADI", "to_stock" => "63", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "TANGGA ALM DP 175", "quantity" => "1", "product_unit" => "BH", "note" => "1324 YAYA AGRO", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "BESI 6 SIP", "quantity" => "30", "product_unit" => "BTG", "note" => "1328 PAK DADANG", "to_stock" => "404", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "BESI 10 YSTI", "quantity" => "100", "product_unit" => "BTG", "note" => "1328 PAK DADANG", "to_stock" => "330", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "2", "product_unit" => "ZAK", "note" => "1330 RUDI", "to_stock" => "213", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "ASIA 40 / 40 CORSO GREY", "quantity" => "12", "product_unit" => "DUS", "note" => "1330 RUDI", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "LEMKRA NAT FS 201 1 KG", "quantity" => "1", "product_unit" => "BKS", "note" => "1330 RUDI", "to_stock" => "22", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2604", "product_name" => "WAVIN D 2'' D", "quantity" => "2", "product_unit" => "BTG", "note" => "1339 JUAL", "to_stock" => "132", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "M 2605", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "100", "transaction_type" => "MASUK"],
            ["date" => "45535", "transaction_code" => "S 2606", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "20", "product_unit" => "ZAK", "note" => "1333 H. BENI", "to_stock" => "193", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2606", "product_name" => "BESI 10 MSI", "quantity" => "50", "product_unit" => "BTG", "note" => "1333 H. BENI", "to_stock" => "305", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2606", "product_name" => "MULTIPLEX 8 MM", "quantity" => "1", "product_unit" => "LBR", "note" => "1343 UJANG ODENG", "to_stock" => "33", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2606", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "24", "product_unit" => "BTG", "note" => "1199 TRIMITRA", "to_stock" => "76", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2606", "product_name" => "KEMTONE INT 25 KG NBD", "quantity" => "1", "product_unit" => "PIL", "note" => "1350 BAITURAHMAN", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "M 2607", "product_name" => "GENTONG TERASO PUTIH LR-DL", "quantity" => "3", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45535", "transaction_code" => "M 2607", "product_name" => "GENTONG TERASO HITAM LR-DL", "quantity" => "4", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45535", "transaction_code" => "T 2608", "product_name" => "COLORAN YELL", "quantity" => "3", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "63", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "T 2608", "product_name" => "COLORAN WHITE", "quantity" => "2", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "76", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "T 2608", "product_name" => "COLORAN YOX", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "75", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "T 2608", "product_name" => "COLORAN BLACK", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "74", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2609", "product_name" => "ASIA 25 / 25 ROYAL BLUE", "quantity" => "4", "product_unit" => "dus", "note" => "1353 JUAL", "to_stock" => "58", "transaction_type" => "KELUAR"],
            ["date" => "45535", "transaction_code" => "S 2609", "product_name" => "IKEMA 25 / 40 21210 PUTIH", "quantity" => "15", "product_unit" => "DUS", "note" => "1354 JUAL", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "MU 380 / 40 KG", "quantity" => "3", "product_unit" => "SAK", "note" => "0001 HARYADI", "to_stock" => "60", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "MU 301", "quantity" => "5", "product_unit" => "SAK", "note" => "0002 MASJID AL-HIDAYAH", "to_stock" => "15", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "FLEXSEAL FS WALL", "quantity" => "10", "product_unit" => "SAK", "note" => "0002 MASJID AL-HIDAYAH", "to_stock" => "33", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "GRC JAYA", "quantity" => "40", "product_unit" => "LBR", "note" => "0010 KOMINFO", "to_stock" => "113", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "100", "product_unit" => "BTG", "note" => "0010 KOMINFO", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "60", "product_unit" => "BTG", "note" => "0010 KOMINFO", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0018 CI YANTI", "to_stock" => "34", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2610", "product_name" => "ASBES GLB KECIL 300 X 105", "quantity" => "2", "product_unit" => "LBR", "note" => "0021 H. RUDI", "to_stock" => "84", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "B 0122", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "10", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "183", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "B 0122", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "2", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "105", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "B 0123", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "6", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "99", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2611", "product_name" => "PAKU 3'' / 7CM", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2611", "product_name" => "SELANG ELASTIS GARIS 1/2\"", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "M 2612", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "200", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "217", "transaction_type" => "MASUK"],
            ["date" => "45537", "transaction_code" => "M 2612", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "400", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "404", "transaction_type" => "MASUK"],
            ["date" => "45537", "transaction_code" => "M 2612", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "128", "transaction_type" => "MASUK"],
            ["date" => "45537", "transaction_code" => "T 2613", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "100", "product_unit" => "ZAK", "note" => "DESA SURIAN", "to_stock" => "83", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "T 2613", "product_name" => "EMBER ORANGE KECIL", "quantity" => "50", "product_unit" => "BH", "note" => "DESA SURIAN", "to_stock" => "121", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "COMPONE A+", "quantity" => "3", "product_unit" => "SAK", "note" => "0026 KOMINFO", "to_stock" => "31", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "MU 301", "quantity" => "5", "product_unit" => "SAK", "note" => "0003 BU SRI BRI", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "30", "product_unit" => "ZAK", "note" => "0013 H. BENI", "to_stock" => "112", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "6", "product_unit" => "BTG", "note" => "0013 H. BENI", "to_stock" => "122", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0029 TANDANG BAP", "to_stock" => "338", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0029 TANDANG BAP", "to_stock" => "128", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "SEMEN HEBEL UNIMIX 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0029 TANDANG BAP", "to_stock" => "13", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "GRC JAYA", "quantity" => "7", "product_unit" => "LBR", "note" => "0029 TANDANG BAP", "to_stock" => "106", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2614", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "40", "product_unit" => "BTG", "note" => "0010 KOMINFO", "to_stock" => "364", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "ARWANA 40 / 40 DALLAS GREY", "quantity" => "7", "product_unit" => "DUS", "note" => "0049 SINAR TERANG", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "SEMEN TIGA RODA", "quantity" => "20", "product_unit" => "ZAK", "note" => "1187 BU EUIS", "to_stock" => "168", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "BESI 6 SIP", "quantity" => "1", "product_unit" => "BTG", "note" => "0056 BU EUIS", "to_stock" => "403", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "20", "product_unit" => "BTG", "note" => "0056 BU EUIS", "to_stock" => "56", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "WAVIN D 4'' D", "quantity" => "4", "product_unit" => "BTG", "note" => "0056 BU EUIS", "to_stock" => "118", "transaction_type" => "KELUAR"],
            ["date" => "45537", "transaction_code" => "S 2615", "product_name" => "WAVIN D 3'' D", "quantity" => "4", "product_unit" => "BTG", "note" => "0056 BU EUIS", "to_stock" => "127", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "B 0124", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "78", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "BCP SUBRON ENGKEL", "quantity" => "1", "product_unit" => "bh", "note" => "0062 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "LIST GRANIT 60 CM POLOS / H", "quantity" => "1", "product_unit" => "BH", "note" => "0065 JUAL", "to_stock" => "71", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "ARWANA 40 / 40 DALLAS GREY", "quantity" => "1", "product_unit" => "DUS", "note" => "0069 SINAR TERANG", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0069 SINAR TERANG", "to_stock" => "77", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "LIST GRANIT 60 CM POLOS / H", "quantity" => "23", "product_unit" => "BH", "note" => "0072 JUAL", "to_stock" => "48", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0072 JUAL", "to_stock" => "167", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "AM NAT BLACK EBONY", "quantity" => "1", "product_unit" => "BKS", "note" => "0072 JUAL", "to_stock" => "41", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "MULTIPLEX 9 MM", "quantity" => "3", "product_unit" => "LBR", "note" => "0061 KOMINFO", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "0073 CI YANTI", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2616", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "6", "product_unit" => "SAK", "note" => "0075 ENGKONG", "to_stock" => "122", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "25", "product_unit" => "BTG", "note" => "0070 IING TOKO KELINCI", "to_stock" => "74", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "15", "product_unit" => "BTG", "note" => "0070 IING TOKO KELINCI", "to_stock" => "54", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "GRC ROYAL BOARD 6 MM", "quantity" => "15", "product_unit" => "LBR", "note" => "0070 IING TOKO KELINCI", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "SEMEN TIGA RODA", "quantity" => "5", "product_unit" => "ZAK", "note" => "0070 IING TOKO KELINCI", "to_stock" => "162", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "KAYU KASO", "quantity" => "20", "product_unit" => "BTG", "note" => "0070 IING TOKO KELINCI", "to_stock" => "120", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "WERMESH M6", "quantity" => "1", "product_unit" => "LBR", "note" => "0077 TANDANG BAP", "to_stock" => "22", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "WAVIN D 4'' D", "quantity" => "2", "product_unit" => "BTG", "note" => "0077 TANDANG BAP", "to_stock" => "116", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0077 TANDANG BAP", "to_stock" => "120", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2617", "product_name" => "SEMEN WARNA DEMIX AGREGAT 1 KG", "quantity" => "3", "product_unit" => "BKS", "note" => "0077 TANDANG BAP", "to_stock" => "51", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0085 TRIMITRA", "to_stock" => "30", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "65", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "299", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "PAPAN GIPSUM A+", "quantity" => "51", "product_unit" => "LBR", "note" => "0085 TRIMITRA", "to_stock" => "44", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "LISPLANG ROYAL 20 CM / 400 POLOS", "quantity" => "19", "product_unit" => "LBR", "note" => "0085 TRIMITRA", "to_stock" => "52", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "54", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "28", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "94", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "BATA HEBEL 10 X 20 X 60", "quantity" => "166", "product_unit" => "BH", "note" => "0085 TRIMITRA", "to_stock" => "1100", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "MU 200 / 20 KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0095 LUCKY", "to_stock" => "27", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "SELANG AC 5/8\"", "quantity" => "1", "product_unit" => "ROLL", "note" => "0097 RIZKI", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2618", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0099 JUAL", "to_stock" => "111", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "P ALUMUNIUM POLOS COKLAT", "quantity" => "1", "product_unit" => "Bh", "note" => "0082 MULUS MOTOR", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0082 MULUS MOTOR", "to_stock" => "161", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "CLOSET INA PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => "0082 MULUS MOTOR", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "ASIA 25 / 25 ROMA GREY", "quantity" => "4", "product_unit" => "DUS", "note" => "0082 MULUS MOTOR", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "UNO 25 / 40 MARINA GREY", "quantity" => "10", "product_unit" => "DUS", "note" => "0082 MULUS MOTOR", "to_stock" => "39", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "WAVIN D 6'' D", "quantity" => "10", "product_unit" => "BTG", "note" => "0093 KOMINFO", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "BESI 10 MSI", "quantity" => "20", "product_unit" => "BTG", "note" => "0093 KOMINFO", "to_stock" => "285", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "KNEE POLOS RCD 6'' D", "quantity" => "8", "product_unit" => "BH", "note" => "0093 KOMINFO", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45538", "transaction_code" => "S 2619", "product_name" => "KNEE POLOS RCAW 3'' AW", "quantity" => "5", "product_unit" => "BH", "note" => "0108 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 120", "quantity" => "3", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 127", "quantity" => "2", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "7", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 109", "quantity" => "3", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 121", "quantity" => "3", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 128", "quantity" => "3", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2620", "product_name" => "KINGKONG 18 KG 131", "quantity" => "2", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2621", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "300", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "599", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2621", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "194", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "M 2621", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "250", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "250", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "B 0125", "product_name" => "HOLO GIPSUM 4/4 HIJAU / 0.3", "quantity" => "37", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "B 0125", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "50", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "549", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "WAVIN D 2 1/2'' D", "quantity" => "1", "product_unit" => "BTG", "note" => "0111 H. AANG", "to_stock" => "188", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "WAVIN D 3'' D", "quantity" => "6", "product_unit" => "BTG", "note" => "0111 H. AANG", "to_stock" => "121", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "10", "product_unit" => "BTG", "note" => "0111 H. AANG", "to_stock" => "328", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "91", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "458", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "TEE RADIUS RCD 2 1/2\" X 2\" D", "quantity" => "2", "product_unit" => "BH", "note" => "0117 ISP", "to_stock" => "13", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "ASIA 25 / 25 ROMA BROWN", "quantity" => "2", "product_unit" => "DUS", "note" => "0126 JUAL", "to_stock" => "19", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "LEMKRA NAT FS 216 1 KG", "quantity" => "1", "product_unit" => "BKS", "note" => "0126 JUAL", "to_stock" => "9", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2622", "product_name" => "AM NAT WHITE JASMINE", "quantity" => "1", "product_unit" => "BKS", "note" => "0126 JUAL", "to_stock" => "25", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2623", "product_name" => "RAM KOTAK RAM AYAM BULAT", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "AM NAT CREAM LOTUS", "quantity" => "2", "product_unit" => "BKS", "note" => "0119 H. NANANG", "to_stock" => "38", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "CERANOSA 60 / 60 CREAM POLOS", "quantity" => "10", "product_unit" => "DUS", "note" => "0119 H. NANANG", "to_stock" => "285", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "SPANDEX PASIR MERAH", "quantity" => "10", "product_unit" => "LBR", "note" => "0116 IING TK KELINCI", "to_stock" => "30", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "WAVIN D 2 1/2'' D", "quantity" => "2", "product_unit" => "BTG", "note" => "0128 H. BENI", "to_stock" => "186", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "10", "product_unit" => "ZAK", "note" => "0128 H. BENI", "to_stock" => "101", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "20", "product_unit" => "ZAK", "note" => "0124 H. RUDI", "to_stock" => "81", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "MU 380 / 40 KG", "quantity" => "4", "product_unit" => "SAK", "note" => "0121 H. RUDI", "to_stock" => "56", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "BATA HEBEL 10 X 20 X 60", "quantity" => "249", "product_unit" => "BH", "note" => "0121 H. RUDI", "to_stock" => "851", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2624", "product_name" => "ASIA 25 / 25 ROYAL BLUE", "quantity" => "5", "product_unit" => "dus", "note" => "0154 JUAL", "to_stock" => "53", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "100", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "94", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "128", "product_unit" => "BTG", "note" => "0085 TRIMITRA", "to_stock" => "122", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "WAVIN AW 2 1/2'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0145 JUAL", "to_stock" => "59", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0145 JUAL", "to_stock" => "119", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "BATU PROFIL P-URIL", "quantity" => "12", "product_unit" => "BH", "note" => "0145 JUAL", "to_stock" => "71", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "10", "product_unit" => "BTG", "note" => "0145 JUAL", "to_stock" => "46", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "TEE RCD 6'' D", "quantity" => "1", "product_unit" => "BH", "note" => "0158 KOMINFO", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "0158 KOMINFO", "to_stock" => "28", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0159 HARYADI", "to_stock" => "54", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2625", "product_name" => "SEMEN TIGA RODA", "quantity" => "2", "product_unit" => "ZAK", "note" => "0159 HARYADI", "to_stock" => "159", "transaction_type" => "KELUAR"],
            ["date" => "45539", "transaction_code" => "S 2626", "product_name" => "BATA HEBEL 10 X 20 X 60", "quantity" => "166", "product_unit" => "BH", "note" => "RETUR 0137", "to_stock" => "1017", "transaction_type" => "MASUK"],
            ["date" => "45539", "transaction_code" => "S 2627", "product_name" => "KINGKONG 18 KG 131", "quantity" => "1", "product_unit" => "PIL", "note" => "0149 CI YANTI", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "M 2628", "product_name" => "BATA HEBEL 7,5 X 20 X 60", "quantity" => "1400", "product_unit" => "KPG", "note" => "MASUK", "to_stock" => "1400", "transaction_type" => "MASUK"],
            ["date" => "45540", "transaction_code" => "M 2629", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "200", "product_unit" => "ZAK", "note" => "MASUK", "to_stock" => "272", "transaction_type" => "MASUK"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "KNEE POLOS RCAW 1/2'' AW", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "SPECTRUM WP 25 KG WHITE", "quantity" => "5", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "SPECTRUM 25 KG YELL", "quantity" => "2", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "SPECTRUM WP 5 KG PUTIH", "quantity" => "8", "product_unit" => "GLN", "note" => "DISPLAY", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "TOPSEAL 4L NO 16", "quantity" => "4", "product_unit" => "GLN", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "METROLITE 5 KG", "quantity" => "12", "product_unit" => "GLN", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "KAWAT BETON 25 KG", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2630", "product_name" => "SEMEN PUTIH", "quantity" => "1", "product_unit" => "ZAK", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "BATA HEBEL 7,5 X 20 X 60", "quantity" => "222", "product_unit" => "KPG", "note" => "0137 TRIMITRA", "to_stock" => "1178", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "25", "product_unit" => "ZAK", "note" => "0168 KOMINFO", "to_stock" => "56", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0173 H. AANG", "to_stock" => "149", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "P PVC MY DOOR ABU", "quantity" => "2", "product_unit" => "BH", "note" => "0180 VESRA SKM", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "50", "product_unit" => "ZAK", "note" => "0186 TRIMITRA", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "SEMEN TIGA RODA", "quantity" => "5", "product_unit" => "ZAK", "note" => "0188 JUAL", "to_stock" => "144", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "BESI 6 BKI", "quantity" => "1", "product_unit" => "BTG", "note" => "0192 JUAL", "to_stock" => "318", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "MU 301", "quantity" => "2", "product_unit" => "SAK", "note" => "0199 BU SRI BRI", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "MU 200 / 20 KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0199 BU SRI BRI", "to_stock" => "26", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2631", "product_name" => "BATU PROFIL P-URIL", "quantity" => "5", "product_unit" => "BH", "note" => "0201 JUAL", "to_stock" => "66", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "GLASBOK PARANG", "quantity" => "2", "product_unit" => "DUS", "note" => "0189 BU HJ. IDA", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0189 BU HJ. IDA", "to_stock" => "143", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "GRC PINK BOARD", "quantity" => "2", "product_unit" => "LBR", "note" => "0189 BU HJ. IDA", "to_stock" => "9", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "ARWANA 40 /40 6686 CREAM", "quantity" => "3", "product_unit" => "DUS", "note" => "0189 BU HJ. IDA", "to_stock" => "97", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "8", "product_unit" => "BTG", "note" => "0197 TEMI POLRES", "to_stock" => "173", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0207 JUAL", "to_stock" => "142", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "6", "product_unit" => "BTG", "note" => "0208 H. AANG", "to_stock" => "68", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2632", "product_name" => "SELANG BENANG SINGAMAS HIJAU 5/8\" 50M", "quantity" => "1", "product_unit" => "ROL", "note" => "0211 HJ. YENI", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2633", "product_name" => "MULTIPLEX 18 MM F", "quantity" => "2", "product_unit" => "LBR", "note" => "0214 ZIA MOTOR", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2633", "product_name" => "P PVC OVAL CREAM", "quantity" => "1", "product_unit" => "BH", "note" => " 0219 GURU YEYET", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2633", "product_name" => "CLOSED LOLO PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => " 0219 GURU YEYET", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "S 2633", "product_name" => "P PVC POLOS PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => " 0219 GURU YEYET", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45540", "transaction_code" => "B 0126", "product_name" => "HOLO GIPSUM 4/4 PUTIH", "quantity" => "60", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "398", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "B 0127", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "6", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "87", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "B 0127", "product_name" => "FLEXSEAL FS ACIAN", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "40", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "M 2634", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "204", "product_unit" => "ZAK", "note" => "MASUK", "to_stock" => "210", "transaction_type" => "MASUK"],
            ["date" => "45541", "transaction_code" => "M 2635", "product_name" => "GEROBAK PASIR DASGLUK ORANGE", "quantity" => "10", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "14", "transaction_type" => "MASUK"],
            ["date" => "45541", "transaction_code" => "M 2635", "product_name" => "GEROBAK PASIR DASH GLUCK HIJAU", "quantity" => "5", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "5", "transaction_type" => "MASUK"],
            ["date" => "45541", "transaction_code" => "M 2635", "product_name" => "GEROBAK PASIR DRAGON FLY", "quantity" => "2", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45541", "transaction_code" => "S 2636", "product_name" => "SELANG WATERPAS TEBAL 1/4\"", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2636", "product_name" => "KNEE POLOS RCAW 2'' AW", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2636", "product_name" => "PURATEX PUTIH / 25 KG", "quantity" => "3", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "CERANOSA 60 / 60 GC002", "quantity" => "1", "product_unit" => "Dus", "note" => "0222 JUAL", "to_stock" => "21", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "50", "product_unit" => "ZAK", "note" => "0186 TRIMITRA", "to_stock" => "160", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "10", "product_unit" => "BTG", "note" => "0232 CI YANTI", "to_stock" => "163", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "MULTIPLEX 9 MM", "quantity" => "2", "product_unit" => "LBR", "note" => "0232 CI YANTI", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN AW 1'' AW", "quantity" => "2", "product_unit" => "BTG", "note" => "0244 BILY PT SPU", "to_stock" => "69", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN D 2'' D", "quantity" => "4", "product_unit" => "BTG", "note" => "0247 H. RUDI", "to_stock" => "128", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN AW 1'' AW", "quantity" => "5", "product_unit" => "BTG", "note" => "0251 H. RUDI", "to_stock" => "64", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "4", "product_unit" => "BTG", "note" => "0249 JUAL", "to_stock" => "324", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2637", "product_name" => "WAVIN AW 1 1/2'' AW", "quantity" => "6", "product_unit" => "BTG", "note" => "0249 JUAL", "to_stock" => "47", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "P PVC POLOS PINK", "quantity" => "2", "product_unit" => "BH", "note" => "0241 H. AANG", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "P PVC POLOS CREAM", "quantity" => "2", "product_unit" => "BH", "note" => "0241 H. AANG", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "P PVC POLOS BIRU", "quantity" => "4", "product_unit" => "BH", "note" => "0241 H. AANG", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "BESI 8 MSTI", "quantity" => "10", "product_unit" => "BTG", "note" => "0248 BU TUTI", "to_stock" => "129", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "BESI 12 MSI", "quantity" => "5", "product_unit" => "BTG", "note" => "0248 BU TUTI", "to_stock" => "228", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "3", "product_unit" => "BTG", "note" => "0254 HJ. YENI", "to_stock" => "160", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "ASIA 20 / 20 GALAXY BLACK", "quantity" => "1", "product_unit" => "DUS", "note" => "0257 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "MULTIPLEX 18 MM HP", "quantity" => "1", "product_unit" => "LBR", "note" => "0260 JUAL", "to_stock" => "9", "transaction_type" => "KELUAR"],
            ["date" => "45541", "transaction_code" => "S 2638", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "0264 CI YANTI", "to_stock" => "267", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "B 0128", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "20", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "26", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "B 0128", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "2", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "265", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "B 0129", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "25", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "T 2639", "product_name" => "COLORAN BLUE", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "23", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "T 2639", "product_name" => "COLORAN GREEN", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "56", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "T 2639", "product_name" => "COLORAN WHITE", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "75", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "T 2639", "product_name" => "COLORAN YELL", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "62", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2640", "product_name" => "PAKU 4'' / 10CM", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "7", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2640", "product_name" => "LEM FOX KAYU 1/2 KG BIRU", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "WAVIN D 2 1/2'' D", "quantity" => "25", "product_unit" => "BTG", "note" => "1103 PT HALEYORA", "to_stock" => "161", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "MELAMIN KUNCI PUTIH GLOSS", "quantity" => "3", "product_unit" => "LBR", "note" => "0271 JUAL", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "WAVIN D 3'' D", "quantity" => "1", "product_unit" => "BTG", "note" => "0272 H. RUDI", "to_stock" => "120", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "OK 20 / 20 PINK", "quantity" => "4", "product_unit" => "DUS", "note" => "0275 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "KINGKONG 18 KG 121", "quantity" => "1", "product_unit" => "PIL", "note" => "0280 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "2", "product_unit" => "BTG", "note" => "0294 JUAL", "to_stock" => "158", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2641", "product_name" => "GLASBOK PARANG", "quantity" => "2", "product_unit" => "DUS", "note" => "0296 BU ANI", "to_stock" => "40", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "BESI 6 SIP", "quantity" => "10", "product_unit" => "BTG", "note" => "0298 H. AANG", "to_stock" => "393", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "BESI 10 MSI", "quantity" => "10", "product_unit" => "BTG", "note" => "0298 H. AANG", "to_stock" => "275", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "BESI 8 MSTI", "quantity" => "10", "product_unit" => "BTG", "note" => "0298 H. AANG", "to_stock" => "119", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "5", "product_unit" => "BTG", "note" => "0298 H. AANG", "to_stock" => "63", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "SPANDEX PASIR HITAM", "quantity" => "5", "product_unit" => "LBR", "note" => "0298 H. AANG", "to_stock" => "30", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "MULTIPLEX 18 MM F", "quantity" => "1", "product_unit" => "LBR", "note" => "0307 H. DAENG", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "LISPLANG ROYAL 20 CM KAYU / 405", "quantity" => "7", "product_unit" => "LBR", "note" => "0307 H. DAENG", "to_stock" => "84", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2642", "product_name" => "LISPLANG ROYAL 20 CM KAYU / 405", "quantity" => "5", "product_unit" => "LBR", "note" => "0327 H. DAENG", "to_stock" => "79", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0282 PAK TOTO", "to_stock" => "323", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "TOPSEAL 20L NO 03", "quantity" => "2", "product_unit" => "PIL", "note" => "0282 PAK TOTO", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "T LUCKY GOLD AB 500 L", "quantity" => "1", "product_unit" => "BH", "note" => "0282 PAK TOTO", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "2", "product_unit" => "BTG", "note" => "0283 PAK TOTO", "to_stock" => "321", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "VELLINO 60 / 60 TANBI GIARDINO WHITE", "quantity" => "39", "product_unit" => "DUS", "note" => "0297 PAK DODO", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "0297 PAK DODO", "to_stock" => "26", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "FLEXSEAL FS ACIAN", "quantity" => "8", "product_unit" => "SAK", "note" => "0310 JUAL", "to_stock" => "32", "transaction_type" => "KELUAR"],
            ["date" => "45542", "transaction_code" => "S 2643", "product_name" => "CASTING A+", "quantity" => "2", "product_unit" => "SAK", "note" => "0321 JUAL", "to_stock" => "107", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "M 2644", "product_name" => "C - BAJA RINGAN 1.0 MM", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "127", "transaction_type" => "MASUK"],
            ["date" => "45544", "transaction_code" => "M 2644", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "50", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "113", "transaction_type" => "MASUK"],
            ["date" => "45544", "transaction_code" => "M 2644", "product_name" => "RENG BAJA BIRU", "quantity" => "60", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "60", "transaction_type" => "MASUK"],
            ["date" => "45544", "transaction_code" => "M 2645", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "210", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "329", "transaction_type" => "MASUK"],
            ["date" => "45544", "transaction_code" => "T 2646", "product_name" => "COLORAN YOX", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "74", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "T 2646", "product_name" => "COLORAN WHITE", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "74", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2647", "product_name" => "FYBER POLOS TEBAL PUTIH 30M", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2647", "product_name" => "PAKU 2'' / 5CM", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "7", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0332 HARYADI", "to_stock" => "52", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "SEMEN TIGA RODA", "quantity" => "2", "product_unit" => "ZAK", "note" => "0332 HARYADI", "to_stock" => "140", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0335 BAITU RAHMAN", "to_stock" => "139", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "ROSTER KERAMIK 20/20 RO 6 B / PUTIH", "quantity" => "3", "product_unit" => "BH", "note" => "0337 JUAL", "to_stock" => "57", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "30", "product_unit" => "ZAK", "note" => "0340 H. BENI", "to_stock" => "130", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "SEMEN TIGA RODA", "quantity" => "30", "product_unit" => "ZAK", "note" => "1187 EUIS DINKES", "to_stock" => "109", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "20", "product_unit" => "ZAK", "note" => "0336 PAK DODO", "to_stock" => "110", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "MU 380 / 40 KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0336 PAK DODO", "to_stock" => "51", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2648", "product_name" => "CERANOSA 60 / 60 GC002", "quantity" => "2", "product_unit" => "Dus", "note" => "0341 JUAL", "to_stock" => "19", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "10", "product_unit" => "SAK", "note" => "0350 PAK TARKIM", "to_stock" => "319", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "C - BAJA RINGAN 1.0 MM", "quantity" => "60", "product_unit" => "BTG", "note" => "0350 PAK TARKIM", "to_stock" => "67", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "SEMEN TIGA RODA", "quantity" => "3", "product_unit" => "ZAK", "note" => "0358 IING TK KELINCI", "to_stock" => "106", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "BESI 10 MSI", "quantity" => "1", "product_unit" => "BTG", "note" => "0358 IING TK KELINCI", "to_stock" => "274", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "BESI 8 MSTI", "quantity" => "6", "product_unit" => "BTG", "note" => "0358 IING TK KELINCI", "to_stock" => "113", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "RENG BAJA BIRU", "quantity" => "60", "product_unit" => "BTG", "note" => "0358 IING TK KELINCI", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "IKEMA 25 / 40 21210 PUTIH", "quantity" => "1", "product_unit" => "DUS", "note" => "0360 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "GLASBOK QUADRA HIJAU", "quantity" => "1", "product_unit" => "DUS", "note" => "0361 JUAL", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "KINGKONG 18 KG 131", "quantity" => "1", "product_unit" => "PIL", "note" => "0368 JUAL", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2649", "product_name" => "CASTING A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0369 JUAL", "to_stock" => "106", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2650", "product_name" => "ASIA 25 / 25 ROMA GREY", "quantity" => "2", "product_unit" => "DUS", "note" => "RETUR PBL 09/003", "to_stock" => "13", "transaction_type" => "MASUK"],
            ["date" => "45544", "transaction_code" => "S 2651", "product_name" => "WAVIN D 2'' D", "quantity" => "1", "product_unit" => "BTG", "note" => "0380 ANDIKA ISP", "to_stock" => "127", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "S 2651", "product_name" => "WAVIN D 2 1/2'' D", "quantity" => "1", "product_unit" => "BTG", "note" => "0380 ANDIKA ISP", "to_stock" => "160", "transaction_type" => "KELUAR"],
            ["date" => "45544", "transaction_code" => "B 0130", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "260", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "T 2652", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "125", "product_unit" => "ZAK", "note" => "DESA SURIAN", "to_stock" => "135", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "LEM PVC ASAHI KALENG", "quantity" => "2", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "LEM PVC ISARPLAS KALENG", "quantity" => "3", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "LEM FOX IBON 600 GR / 1 KG", "quantity" => "1", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "1", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "LEM FOX IBON 300 GR / 1/2 KG", "quantity" => "1", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "1", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "LEM FOX KAYU 1 KG MERAH", "quantity" => "2", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "TALANG AIR 1/2 LING MASPION 6\"", "quantity" => "10", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "20", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2653", "product_name" => "ALAT TALANG BULAT SAMBUNGAN 6\" TM", "quantity" => "40", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "49", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "M 2654", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "204", "product_unit" => "ZAK", "note" => "MASUK", "to_stock" => "314", "transaction_type" => "MASUK"],
            ["date" => "45545", "transaction_code" => "S 2655", "product_name" => "KARPET 55 MERAH 50M", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2655", "product_name" => "METROLITE 25 KG", "quantity" => "5", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "T 2652", "product_name" => "SELANG ELASTIS GARIS 3/4\"", "quantity" => "1", "product_unit" => "ROLL", "note" => "DESA SURIAN", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "T 2656", "product_name" => "COLORAN BLACK", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "73", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "COVE 60 / 60 LONDON [1,44]", "quantity" => "13", "product_unit" => "DUS", "note" => "0392 KOMINFO", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "20", "product_unit" => "ZAK", "note" => "0392 KOMINFO", "to_stock" => "294", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "15", "product_unit" => "SAK", "note" => "0394 RIZKI", "to_stock" => "304", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "FYBER POLOS TEBAL PUTIH 30M", "quantity" => "1", "product_unit" => "ROLL", "note" => "0401 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "KEMTONE EXT 25 KG TBM", "quantity" => "1", "product_unit" => "PIL", "note" => "0407 BU ICIH ", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "WAVIN D 2'' D", "quantity" => "6", "product_unit" => "BTG", "note" => "0419 JUAL", "to_stock" => "121", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "WAVIN D 1 1/2'' D", "quantity" => "4", "product_unit" => "BTG", "note" => "0419 JUAL", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "10", "product_unit" => "BTG", "note" => "0419 JUAL", "to_stock" => "148", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2657", "product_name" => "WAVIN AW 1'' AW", "quantity" => "4", "product_unit" => "BTG", "note" => "0419 JUAL", "to_stock" => "60", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0420 JUAL", "to_stock" => "134", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "ASIA 25 / 25 ROMA BROWN", "quantity" => "3", "product_unit" => "DUS", "note" => "0420 JUAL", "to_stock" => "16", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "ASIA 25 / 40 NINO CREAM DECOR", "quantity" => "1", "product_unit" => "DUS", "note" => "0420 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "CERANOSA 60 / 60 HITAM POLOS", "quantity" => "2", "product_unit" => "DUS", "note" => "0429 JUAL", "to_stock" => "63", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0416 H. EPON", "to_stock" => "96", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "GENTONG TERASO PUTIH LR-DL", "quantity" => "1", "product_unit" => "BH", "note" => "0416 H. EPON", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "CLOSED LOLO PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => "0437 KUWAIT", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "MONOBLOK TRILIUN SAPPHIRA HITAM", "quantity" => "1", "product_unit" => "BH", "note" => "0437 KUWAIT", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2658", "product_name" => "GENTONG TERASO PUTIH LR-DL", "quantity" => "1", "product_unit" => "BH", "note" => "0440 HARIS", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "MELAMIN LOTUS PUTIH DNM", "quantity" => "2", "product_unit" => "LBR", "note" => "0428 GINGGA", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "STAAL GALVANIS 4/4 x 1,3", "quantity" => "3", "product_unit" => "btg", "note" => "0428 GINGGA", "to_stock" => "24", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "STAAL GALVANIS 2/4 X 1,3", "quantity" => "3", "product_unit" => "BTG", "note" => "0428 GINGGA", "to_stock" => "23", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "10", "product_unit" => "BTG", "note" => "0428 GINGGA", "to_stock" => "103", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "ASIA 40 / 40 CORSO GREY", "quantity" => "6", "product_unit" => "DUS", "note" => "0426 FARID LANTAS", "to_stock" => "36", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "ARWANA 40 /40 6676 CREAM", "quantity" => "17", "product_unit" => "DUS", "note" => "0426 FARID LANTAS", "to_stock" => "83", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "WASTAFEL VOLK A 306 KOTAK", "quantity" => "1", "product_unit" => "Bh", "note" => "0426 FARID LANTAS", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "MONOBLOK ICE POOL TWO PIECS IC 690", "quantity" => "1", "product_unit" => "dh", "note" => "0426 FARID LANTAS", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "S 2659", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "4", "product_unit" => "SAK", "note" => "0426 FARID LANTAS", "to_stock" => "300", "transaction_type" => "KELUAR"],
            ["date" => "45545", "transaction_code" => "B 0131", "product_name" => "PAPAN GIPSUM A+", "quantity" => "44", "product_unit" => "LBR", "note" => "BANGUNAN OTISTA", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "M 2660", "product_name" => "COMPONE A+", "quantity" => "50", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "76", "transaction_type" => "MASUK"],
            ["date" => "45546", "transaction_code" => "M 2660", "product_name" => "PAPAN GIPSUM A+", "quantity" => "212", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "212", "transaction_type" => "MASUK"],
            ["date" => "45546", "transaction_code" => "B 0132", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "82", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "B 0132", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "129", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "B 0132", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "75", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "ASIA 40 / 40 OSCAR BROWN", "quantity" => "5", "product_unit" => "DUS", "note" => "0450 ROSI MIKA", "to_stock" => "47", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "MU 380 / 40 KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0452 JUAL", "to_stock" => "50", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0457 JUAL", "to_stock" => "147", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0461 BU YAYU", "to_stock" => "86", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "BESI 6 SIP", "quantity" => "10", "product_unit" => "BTG", "note" => "0461 BU YAYU", "to_stock" => "383", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "BESI 10 MSI", "quantity" => "15", "product_unit" => "BTG", "note" => "0461 BU YAYU", "to_stock" => "259", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2662", "product_name" => "LASER PLAS 3M X 105 BENING", "quantity" => "2", "product_unit" => "LBR", "note" => "0459 RAHMAWATI", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "WAVIN D 3'' D", "quantity" => "6", "product_unit" => "BTG", "note" => "0460 EUIS DINKES", "to_stock" => "114", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "WAVIN AW 1'' AW", "quantity" => "8", "product_unit" => "BTG", "note" => "0460 EUIS DINKES", "to_stock" => "52", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "5", "product_unit" => "BTG", "note" => "0460 EUIS DINKES", "to_stock" => "142", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0463 HENDRA", "to_stock" => "299", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "ARWANA 40 /40 6686 GREY", "quantity" => "10", "product_unit" => "DUS", "note" => "0463 HENDRA", "to_stock" => "64", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "TIERRA 25 / 25 BUBLE ABU", "quantity" => "13", "product_unit" => "DUS", "note" => "0463 HENDRA", "to_stock" => "13", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0475 HARYADI", "to_stock" => "48", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "KINGKONG 18 KG 131", "quantity" => "1", "product_unit" => "PIL", "note" => "0472 CI YANTI", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2663", "product_name" => "MULTIPLEX 6 MM BC", "quantity" => "35", "product_unit" => "LBR", "note" => "0485 HERI", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "B 0133", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "1", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "B 0133", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "5", "product_unit" => "BTG", "note" => "BANGUNAN OTISTA", "to_stock" => "137", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "T 2661", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "125", "product_unit" => "ZAK", "note" => "DESA SURIAN", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2664", "product_name" => "VINILEX 25KG P - 300", "quantity" => "1", "product_unit" => "PIL", "note" => "0439 LASKA", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2664", "product_name" => "VINILEX 25KG P - 300", "quantity" => "1", "product_unit" => "PIL", "note" => "0490 LASKA", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2664", "product_name" => "AM NAT WHITE JASMINE", "quantity" => "3", "product_unit" => "BKS", "note" => "0490 LASKA", "to_stock" => "22", "transaction_type" => "KELUAR"],
            ["date" => "45546", "transaction_code" => "S 2664", "product_name" => "ARWANA 40 /40 6686 CREAM", "quantity" => "30", "product_unit" => "DUS", "note" => "0489 MAS PARNO", "to_stock" => "67", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "T 2665", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "DODI SUPERAPO", "to_stock" => "77", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "T 2665", "product_name" => "KAWAT BERONJONG 2M X 1M X 50M", "quantity" => "4", "product_unit" => "ROLL", "note" => "DODI SUPERAPO", "to_stock" => "145", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "T 2665", "product_name" => "MULTIPLEX 9 MM", "quantity" => "2", "product_unit" => "LBR", "note" => "DODI SUPERAPO", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "B 0134", "product_name" => "PAPAN GIPSUM A+", "quantity" => "30", "product_unit" => "LBR", "note" => "BANGUNAN OTISTA", "to_stock" => "182", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "B 0134", "product_name" => "FLEXSEAL FS ACIAN", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "27", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "B 0134", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "10", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "67", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "B 0134", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "289", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "B 0134", "product_name" => "FLEXSEAL FS WALL", "quantity" => "1", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "32", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "M 2666", "product_name" => "VELLINO 60 / 60 TANBI GIARDINO WHITE", "quantity" => "78", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "78", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2667", "product_name" => "ULTRAPROOF 25 KG WHITE", "quantity" => "2", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2667", "product_name" => "ULTRAPROOF 5 KG PASTEL GREY", "quantity" => "8", "product_unit" => "GLN", "note" => "MASUK", "to_stock" => "8", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2667", "product_name" => "ULTRAPROOF 5 KG APRICOT", "quantity" => "4", "product_unit" => "GLN", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2668", "product_name" => "KNEE GREST 3\"", "quantity" => "75", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "75", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2668", "product_name" => "LEM PVC ISARPLAS KALENG", "quantity" => "2", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "5", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "S 2669", "product_name" => "LEM FOX IBON 300 GR / 1/2 KG", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "KNEE POLOS RCD 6'' D", "quantity" => "1", "product_unit" => "BH", "note" => "0492 KOMINFO", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "20", "product_unit" => "ZAK", "note" => "0495 H. BENI", "to_stock" => "269", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "GLASBOK MEGA MENDUNG", "quantity" => "2", "product_unit" => "DUS", "note" => "0495 H. BENI", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "6", "product_unit" => "BTG", "note" => "0500 JUAL", "to_stock" => "211", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "KEMTONE INT 25 KG TBA", "quantity" => "1", "product_unit" => "PIL", "note" => "0498 SIAGIAN", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0498 SIAGIAN", "to_stock" => "297", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "PAPAN GIPSUM A+", "quantity" => "2", "product_unit" => "LBR", "note" => "0498 SIAGIAN", "to_stock" => "180", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "SEMEN TIGA RODA", "quantity" => "5", "product_unit" => "ZAK", "note" => "0502 IING TK KELINCI", "to_stock" => "81", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2670", "product_name" => "TRIPLEX 3 MM", "quantity" => "2", "product_unit" => "LBR", "note" => "0503 CI YANTI", "to_stock" => "45", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "ROMAN 20 / 20 22730", "quantity" => "1", "product_unit" => "DUS", "note" => "0505 JUAL", "to_stock" => "39", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "KNEE POLOS RCD 6'' D", "quantity" => "1", "product_unit" => "BH", "note" => "0519 KOMINFO", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0529 HARYADI", "to_stock" => "46", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0530 JUAL", "to_stock" => "74", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "MULTIPLEX 18 MM F", "quantity" => "1", "product_unit" => "LBR", "note" => "0533 ZIA MOTOR", "to_stock" => "16", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "ARTILE 30 / 60 DARK MAHOGANY", "quantity" => "2", "product_unit" => "DUS", "note" => "0534 JUAL", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "VELLINO 60 / 60 TANBI GIARDINO WHITE", "quantity" => "16", "product_unit" => "DUS", "note" => "0532 PAK DODO", "to_stock" => "62", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "RENG BAJA RINGAN 0.3", "quantity" => "3", "product_unit" => "BTG", "note" => "0543 ERNA", "to_stock" => "119", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2671", "product_name" => "SOLAR TUFF 210 X 80 BENING", "quantity" => "3", "product_unit" => "LBR", "note" => "0543 ERNA", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "T 2672", "product_name" => "COLORAN YOX", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "73", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "T 2672", "product_name" => "COLORAN YELL", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "61", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "M 2673", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "200", "product_unit" => "ZAK", "note" => "MASUK", "to_stock" => "204", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2674", "product_name" => "STAAL GALVANIS 2/4 X 1,3", "quantity" => "30", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "53", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "M 2674", "product_name" => "STAAL GALVANIS 2/4 X 1,6", "quantity" => "50", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "50", "transaction_type" => "MASUK"],
            ["date" => "45547", "transaction_code" => "S 2675", "product_name" => "COLORAN YELL", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "60", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2675", "product_name" => "COLORAN WHITE", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "73", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2676", "product_name" => "SEMEN TIGA RODA", "quantity" => "2", "product_unit" => "ZAK", "note" => "0541 SOLIHIN", "to_stock" => "79", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2676", "product_name" => "STAAL GALVANIS 2/4 X 1,6", "quantity" => "50", "product_unit" => "BTG", "note" => "0502 IING TK KELINCI", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2676", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0553 IING TK KELINCI", "to_stock" => "69", "transaction_type" => "KELUAR"],
            ["date" => "45547", "transaction_code" => "S 2676", "product_name" => "ARWANA 40 /40 6686 GREY", "quantity" => "5", "product_unit" => "DUS", "note" => "0557 JUAL", "to_stock" => "59", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "M 2677", "product_name" => "SENG 55 / 02", "quantity" => "1", "product_unit" => "ROLL", "note" => "MASUK", "to_stock" => "3", "transaction_type" => "MASUK"],
            ["date" => "45552", "transaction_code" => "M 2677", "product_name" => "SENG GELOMBANG 180 X 80 X 0,2", "quantity" => "25", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "39", "transaction_type" => "MASUK"],
            ["date" => "45552", "transaction_code" => "M 2677", "product_name" => "GRC ROYAL BOARD 6 MM", "quantity" => "25", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "30", "transaction_type" => "MASUK"],
            ["date" => "45552", "transaction_code" => "M 2677", "product_name" => "TANGGA ALM AL 09", "quantity" => "2", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45552", "transaction_code" => "B 0135", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "B 0136", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "62", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "T 2678", "product_name" => "WAVIN D 4'' D", "quantity" => "101", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "15", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "T 2678", "product_name" => "WAVIN D 6'' D", "quantity" => "8", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "T 2678", "product_name" => "V SOK RCD 6\" X 4\" D", "quantity" => "10", "product_unit" => "BH", "note" => "H. ARIS", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "SEMEN TIGA RODA", "quantity" => "1", "product_unit" => "ZAK", "note" => "0568 HARYADI", "to_stock" => "68", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "10", "product_unit" => "SAK", "note" => "0567 MASJID AL-HIDAYAH", "to_stock" => "287", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "FLEXSEAL FS WALL", "quantity" => "5", "product_unit" => "SAK", "note" => "0567 MASJID AL-HIDAYAH", "to_stock" => "27", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0573 ADE BORDIR", "to_stock" => "203", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "P ALUMUNIUM POLOS PUTIH", "quantity" => "2", "product_unit" => "BH", "note" => "0573 ADE BORDIR", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "ARWANA 40 /40 6676 CREAM", "quantity" => "3", "product_unit" => "DUS", "note" => "0586 FARID LANTAS", "to_stock" => "80", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "BESI 6 SIP", "quantity" => "30", "product_unit" => "BTG", "note" => "0578 DADANG CICADAS", "to_stock" => "353", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "BESI 8 YES", "quantity" => "100", "product_unit" => "BTG", "note" => "0578 DADANG CICADAS", "to_stock" => "300", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2679", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0574 IING TK KELINCI", "to_stock" => "58", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "WAVIN AW 1'' AW", "quantity" => "4", "product_unit" => "BTG", "note" => "0581 H. RUDI", "to_stock" => "48", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "WAVIN D 3'' D", "quantity" => "8", "product_unit" => "BTG", "note" => "0581 H. RUDI", "to_stock" => "106", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "MU 380 / 40 KG", "quantity" => "2", "product_unit" => "SAK", "note" => "0585 H. RUDI", "to_stock" => "44", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "ARWANA 40 /40 6686 CREAM", "quantity" => "30", "product_unit" => "DUS", "note" => "0588 MAS PARNO", "to_stock" => "37", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "2", "product_unit" => "ZAK", "note" => "0593 FARID LANTAS", "to_stock" => "201", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "50", "product_unit" => "ZAK", "note" => "0186 TRIMITRA", "to_stock" => "219", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "SEMEN TIGA RODA", "quantity" => "30", "product_unit" => "ZAK", "note" => "1187 EUIS DINKES", "to_stock" => "28", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "WASTAFEL VOLK PUTIH", "quantity" => "1", "product_unit" => "BH", "note" => "0589 H. EPON", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2680", "product_name" => "CERANOSA 60 / 60 CREAM POLOS", "quantity" => "77", "product_unit" => "DUS", "note" => "0589 H. EPON", "to_stock" => "208", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "10", "product_unit" => "SAK", "note" => "0591 TARKIM", "to_stock" => "277", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "3", "product_unit" => "BTG", "note" => "0591 TARKIM", "to_stock" => "318", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "3", "product_unit" => "BTG", "note" => "0591 TARKIM", "to_stock" => "134", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "DOP 4\"", "quantity" => "4", "product_unit" => "BH", "note" => "0601 EUIS DINKES", "to_stock" => "71", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "20", "product_unit" => "SAK", "note" => "0601 EUIS DINKES", "to_stock" => "257", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "BESI URIL 10 CBS", "quantity" => "10", "product_unit" => "BTG", "note" => "0601 EUIS DINKES", "to_stock" => "192", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "WAVIN AW 1'' AW", "quantity" => "6", "product_unit" => "BTG", "note" => "0609 EUIS DINKES", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "WAVIN D 1 1/2'' D", "quantity" => "1", "product_unit" => "BTG", "note" => "0609 EUIS DINKES", "to_stock" => "41", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2681", "product_name" => "COMPONE A+", "quantity" => "3", "product_unit" => "SAK", "note" => "0610 KOMINFO", "to_stock" => "69", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "S 2682", "product_name" => "T LUCKY GOLD TM 500 L", "quantity" => "1", "product_unit" => "BH", "note" => "0583 ADE BORDIR", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45552", "transaction_code" => "T 2683", "product_name" => "COLORAN BLACK", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "B 0137", "product_name" => "LISPLANG KAYU WARNA 20 CM / 300 LEATHER", "quantity" => "3", "product_unit" => "LBR", "note" => "BANGUNAN OTISTA", "to_stock" => "31", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "B 0137", "product_name" => "LISPLANG KAYU WARNA 7,5 CM / 300 OAK", "quantity" => "8", "product_unit" => "LBR", "note" => "BANGUNAN OTISTA", "to_stock" => "101", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "M 2684", "product_name" => "WAVIN D 4'' D", "quantity" => "300", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "315", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "M 2685", "product_name" => "PARALON LISTRIK GANESHA 5/8''", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "100", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "M 2686", "product_name" => "WAVIN D 4'' D", "quantity" => "200", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "515", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "M 2686", "product_name" => "WAVIN D 3'' D", "quantity" => "35", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "141", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "M 2686", "product_name" => "WAVIN D 6'' D", "quantity" => "40", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "40", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "M 2686", "product_name" => "V SOK RCD 6\" X 4\" D", "quantity" => "50", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "50", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "T 2687", "product_name" => "WAVIN D 4'' D", "quantity" => "195", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "320", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "T 2687", "product_name" => "WAVIN D 6'' D", "quantity" => "12", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "28", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "T 2687", "product_name" => "V SOK RCD 6\" X 4\" D", "quantity" => "20", "product_unit" => "BH", "note" => "H. ARIS", "to_stock" => "30", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "T 2687", "product_name" => "LEM PVC ISARPLAS KALENG", "quantity" => "1", "product_unit" => "DUS", "note" => "H. ARIS", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "ROSTER KERAMIK 20/20 RO 7 B / TOBACO", "quantity" => "6", "product_unit" => "BH", "note" => "0619 JUAL", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "GLASBOK TRUNTUM", "quantity" => "2", "product_unit" => "DUS", "note" => "0619 JUAL", "to_stock" => "21", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "2", "product_unit" => "BTG", "note" => "0633 JUAL", "to_stock" => "316", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "ARWANA 40 /40 PUTIH POLOS", "quantity" => "2", "product_unit" => "DUS", "note" => "0637 JUAL", "to_stock" => "239", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0639 JUAL", "to_stock" => "315", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "FLEXSEAL FS GRANITE", "quantity" => "3", "product_unit" => "SAK", "note" => "0646 JUAL", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "PAPAN GIPSUM A+", "quantity" => "2", "product_unit" => "LBR", "note" => "0630 FADILAH HAMORI", "to_stock" => "178", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0630 FADILAH HAMORI", "to_stock" => "68", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2688", "product_name" => "C - BAJA RINGAN 0,7 MM", "quantity" => "1", "product_unit" => "BTG", "note" => "0630 FADILAH HAMORI", "to_stock" => "93", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "25", "product_unit" => "ZAK", "note" => "0634 KOMINFO", "to_stock" => "194", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "AM NAT WHITE JASMINE", "quantity" => "2", "product_unit" => "BKS", "note" => "0645 PAK DODO", "to_stock" => "20", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "HOLO GIPSUM 2/4 PUTIH", "quantity" => "25", "product_unit" => "BTG", "note" => "0645 PAK DODO", "to_stock" => "186", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "PAPAN GIPSUM A+", "quantity" => "6", "product_unit" => "LBR", "note" => "0645 PAK DODO", "to_stock" => "172", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "10", "product_unit" => "ZAK", "note" => "0645 PAK DODO", "to_stock" => "184", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "GEROBAK PASIR DRAGON FLY", "quantity" => "1", "product_unit" => "BH", "note" => "0641 KOMINFO", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "ASIA 30 / 30 MURANO PUTIH", "quantity" => "3", "product_unit" => "DUS", "note" => "0651 JUAL", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0651 JUAL", "to_stock" => "256", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2689", "product_name" => "T LUCKY GOLD TM 300 L", "quantity" => "1", "product_unit" => "BH", "note" => "0667 ADE BORDIR", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2691", "product_name" => "T LUCKY GOLD TM 500 L", "quantity" => "1", "product_unit" => "BH", "note" => "RETUR PBL 09/004", "to_stock" => "1", "transaction_type" => "MASUK"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "BESI 8 MSTI", "quantity" => "6", "product_unit" => "BTG", "note" => "0671 BU TUTI", "to_stock" => "107", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "MU 480 / 25 KG", "quantity" => "4", "product_unit" => "SAK", "note" => "0675 TANEKE TEJA", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "WAVIN AW 3/4'' AW", "quantity" => "1", "product_unit" => "BTG", "note" => "0676 JUAL", "to_stock" => "314", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0672 CI YANTI", "to_stock" => "200", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "TRIPLEX 3 MM", "quantity" => "6", "product_unit" => "LBR", "note" => "0672 CI YANTI", "to_stock" => "39", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "TRIPLEX 3 MM", "quantity" => "1", "product_unit" => "LBR", "note" => "0674 CI YANTI", "to_stock" => "38", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "S 2692", "product_name" => "ASIA 40 / 40 OSCAR BROWN", "quantity" => "40", "product_unit" => "DUS", "note" => "0621 ROSI MIKA", "to_stock" => "7", "transaction_type" => "KELUAR"],
            ["date" => "45553", "transaction_code" => "M 2690", "product_name" => "FLEXSEAL FS WALL", "quantity" => "50", "product_unit" => "SAK", "note" => "MASUK", "to_stock" => "77", "transaction_type" => "MASUK"],
            ["date" => "45554", "transaction_code" => "T 2693", "product_name" => "WAVIN D 4'' D", "quantity" => "180", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "140", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2693", "product_name" => "WAVIN D 6'' D", "quantity" => "12", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "16", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2693", "product_name" => "V SOK RCD 6\" X 4\" D", "quantity" => "18", "product_unit" => "BH", "note" => "H. ARIS", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2693", "product_name" => "WAVIN D 3'' D", "quantity" => "35", "product_unit" => "BTG", "note" => "H. ARIS", "to_stock" => "106", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2694", "product_name" => "SEMEN TIGA RODA", "quantity" => "15", "product_unit" => "ZAK", "note" => "DODI ANDRIAN", "to_stock" => "13", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2694", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "15", "product_unit" => "ZAK", "note" => "DODI ANDRIAN", "to_stock" => "169", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "SOCKET RCAW 2'' AW", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "KNEE POLOS RCD 3'' D", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "KNEE POLOS RCD 4'' D", "quantity" => "1", "product_unit" => "DUS", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "V SOK RCAW 3/4 x 1/2 aw", "quantity" => "1", "product_unit" => "dus", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "ULTRAPROOF 5 KG WHITE", "quantity" => "4", "product_unit" => "GLN", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "ROMATEX TEMBOK 5KG (AGREGAT)", "quantity" => "5", "product_unit" => "GLN", "note" => "DISPLAY NO 554", "to_stock" => "116", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "ROMATEX TEMBOK 5KG (AGREGAT)", "quantity" => "4", "product_unit" => "GLN", "note" => "DISPLAY NO 518", "to_stock" => "112", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "ROMATEX TEMBOK 5KG (AGREGAT)", "quantity" => "4", "product_unit" => "GLN", "note" => "DISPLAY PUTIH", "to_stock" => "108", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2695", "product_name" => "METROLITE 25 KG", "quantity" => "3", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "SPECTRUM 25 KG TBA", "quantity" => "3", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "24", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "COLORTONE 25 KG TBA", "quantity" => "5", "product_unit" => "PIL", "note" => "DISPLAY", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "SPECTRUM 5 KG TBA", "quantity" => "16", "product_unit" => "GLN", "note" => "DISPLAY", "to_stock" => "52", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "SDL 1/2\" TM", "quantity" => "300", "product_unit" => "BH", "note" => "DISPLAY", "to_stock" => "334", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "PENGKI (IKAT @12)", "quantity" => "1", "product_unit" => "LSN", "note" => "DISPLAY", "to_stock" => "1", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2696", "product_name" => "FYBER MULTIPLAS GARIS PUTIH 120X30", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN RED", "quantity" => "2", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "14", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN WHITE", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN YELL", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "59", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN VIOLET", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "31", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN BLACK", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "71", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN YOX", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2697", "product_name" => "COLORAN ROX", "quantity" => "1", "product_unit" => "BKS", "note" => "ISI", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "M 2698", "product_name" => "SEMEN TIGA RODA", "quantity" => "200", "product_unit" => "ZAK", "note" => "MASUK", "to_stock" => "213", "transaction_type" => "MASUK"],
            ["date" => "45554", "transaction_code" => "M 2699", "product_name" => "PURATEX PUTIH / 25 KG", "quantity" => "10", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "10", "transaction_type" => "MASUK"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "GLASBOK TRUNTUM", "quantity" => "1", "product_unit" => "DUS", "note" => "0696 JUAL", "to_stock" => "20", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "PAKU 1 1/2'' / 4CM", "quantity" => "1", "product_unit" => "DUS", "note" => "0697 PAK BILQIS", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "7", "product_unit" => "ZAK", "note" => "0693 LASKA HOTEL", "to_stock" => "193", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "VINILEX 25KG P - 300", "quantity" => "1", "product_unit" => "PIL", "note" => "0692 LASKA HOTEL", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0702 IING TK KELINCI", "to_stock" => "203", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "ARWANA 40 /40 PUTIH POLOS", "quantity" => "8", "product_unit" => "DUS", "note" => "0704 HENDRAWAN", "to_stock" => "231", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "BESI 12 MSI", "quantity" => "4", "product_unit" => "BTG", "note" => "0705 HENDRAWAN", "to_stock" => "224", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2700", "product_name" => "ARWANA 40 /40 PUTIH POLOS", "quantity" => "1", "product_unit" => "DUS", "note" => "0705 HENDRAWAN", "to_stock" => "230", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "T 2701", "product_name" => "SEMEN TIGA RODA", "quantity" => "50", "product_unit" => "ZAK", "note" => "PENDETA LILY", "to_stock" => "153", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "T LUCKY GOLD AB 900 L", "quantity" => "1", "product_unit" => "BH", "note" => "0719 TANDANG BAP", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "ARWANA 40 /40 PUTIH POLOS", "quantity" => "148", "product_unit" => "DUS", "note" => "0716 TRIMITRA", "to_stock" => "82", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "ASIA 25 / 25 ROMBO BLACK", "quantity" => "8", "product_unit" => "DUS", "note" => "0716 TRIMITRA", "to_stock" => "86", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "MONOBLOK VOLK TWO PIECS", "quantity" => "4", "product_unit" => "BH", "note" => "0716 TRIMITRA", "to_stock" => "5", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "ASIA 30 / 30 MURANO PUTIH", "quantity" => "2", "product_unit" => "DUS", "note" => "0728 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0728 JUAL", "to_stock" => "255", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "BESI 6 BKI", "quantity" => "5", "product_unit" => "BTG", "note" => "0711 HENDRAWAN", "to_stock" => "313", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "S 2702", "product_name" => "CLEAN OUT RCD 3\" D", "quantity" => "1", "product_unit" => "BH", "note" => "0729 JUAL", "to_stock" => "69", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "B 0138", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "57", "transaction_type" => "KELUAR"],
            ["date" => "45554", "transaction_code" => "B 0138", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "5", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "188", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "M 2703", "product_name" => "KARBIT MDQ", "quantity" => "2", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "2", "transaction_type" => "MASUK"],
            ["date" => "45555", "transaction_code" => "M 2703", "product_name" => "MULTIPLEX 9 MM", "quantity" => "20", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "28", "transaction_type" => "MASUK"],
            ["date" => "45555", "transaction_code" => "M 2703", "product_name" => "MULTIPLEX 6 MM BC", "quantity" => "30", "product_unit" => "LBR", "note" => "MASUK", "to_stock" => "30", "transaction_type" => "MASUK"],
            ["date" => "45555", "transaction_code" => "M 2704", "product_name" => "IKEMA 30 / 30 PUTIH POLOS", "quantity" => "256", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "256", "transaction_type" => "MASUK"],
            ["date" => "45555", "transaction_code" => "M 2705", "product_name" => "VINILEX 25 KG PUTIH ANTI KUMAN", "quantity" => "10", "product_unit" => "PIL", "note" => "MASUK", "to_stock" => "10", "transaction_type" => "MASUK"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "1", "product_unit" => "ZAK", "note" => "0733 JUAL", "to_stock" => "168", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "FLEXSEAL FS GRANITE", "quantity" => "1", "product_unit" => "SAK", "note" => "0732 JUAL", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "SEMEN MERDEKA 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0743 JUAL", "to_stock" => "254", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "COMPONE A+", "quantity" => "5", "product_unit" => "SAK", "note" => "0738 KOMINFO", "to_stock" => "63", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "GRC JAYA", "quantity" => "15", "product_unit" => "LBR", "note" => "0738 KOMINFO", "to_stock" => "91", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "WAVIN AW 1 1/2'' AW", "quantity" => "5", "product_unit" => "BTG", "note" => "0738 KOMINFO", "to_stock" => "42", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "PAPAN GIPSUM A+", "quantity" => "8", "product_unit" => "LBR", "note" => "0738 KOMINFO", "to_stock" => "164", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "C - BAJA RINGAN 0,75", "quantity" => "7", "product_unit" => "BTG", "note" => "0738 KOMINFO", "to_stock" => "96", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2706", "product_name" => "P ALUMUNIUM JAYCO KACA FULL", "quantity" => "1", "product_unit" => "BH", "note" => "0741 HANI", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2707", "product_name" => "KAWAT BETON 25 KG", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2707", "product_name" => "RAM KOTAK 2CM HIJAU", "quantity" => "1", "product_unit" => "ROLL", "note" => "DISPLAY", "to_stock" => "4", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "ASIA 30 / 30 MURANO PUTIH", "quantity" => "1", "product_unit" => "DUS", "note" => "0745 JUAL", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "BATA HEBEL 7,5 X 20 X 60", "quantity" => "111", "product_unit" => "KPG", "note" => "0747 TRIMITRA", "to_stock" => "1067", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "IKEMA 30 / 30 PUTIH POLOS", "quantity" => "22", "product_unit" => "DUS", "note" => "0716 TRIMITRA", "to_stock" => "234", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "BLOK BOARD BB 18 HP", "quantity" => "3", "product_unit" => "LBR", "note" => "0749 PACIFIC MOTOR", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "LIST KERAMIK 8 / 25 PINK NO.65", "quantity" => "10", "product_unit" => "BH", "note" => "0752 JUAL", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "RAM KOTAK 2CM HIJAU", "quantity" => "2", "product_unit" => "ROLL", "note" => "0760 JUAL", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0761 H. EPON", "to_stock" => "143", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "CERANOSA 60 / 60 GC002", "quantity" => "2", "product_unit" => "Dus", "note" => "0762 JUAL", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2708", "product_name" => "ULTRAPROOF 25 KG DARK BROWN", "quantity" => "1", "product_unit" => "PIL", "note" => "0769 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2709", "product_name" => "ROMAN 30 / 60 EMBOSS CATANIA CREMA", "quantity" => "4", "product_unit" => "DUS", "note" => "0768 BU ANI", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2709", "product_name" => "ARTILE 60 / 60 TAIPAN", "quantity" => "3", "product_unit" => "dus", "note" => "0768 BU ANI", "to_stock" => "35", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2709", "product_name" => "SOLAR TUFF 240 x 80 BENING", "quantity" => "1", "product_unit" => "lbr", "note" => "0768 BU ANI", "to_stock" => "22", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2709", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "3", "product_unit" => "ZAK", "note" => "0768 BU ANI", "to_stock" => "185", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2709", "product_name" => "ROMAN 30 / 60 HALUS CATANIA SAND", "quantity" => "15", "product_unit" => "DUS", "note" => "0768 BU ANI", "to_stock" => "10", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2710", "product_name" => "BESI 8 MSI", "quantity" => "5", "product_unit" => "BTG", "note" => "0705 HENDRAWAN", "to_stock" => "395", "transaction_type" => "KELUAR"],
            ["date" => "45555", "transaction_code" => "S 2711", "product_name" => "BESI 8 MSI", "quantity" => "5", "product_unit" => "BTG", "note" => "RETUR PBL 0005", "to_stock" => "400", "transaction_type" => "MASUK"],
            ["date" => "45556", "transaction_code" => "B 0139", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "2", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "55", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "B 0140", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "50", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "FLEXSEAL FS GRANITE", "quantity" => "2", "product_unit" => "SAK", "note" => "0781 JUAL", "to_stock" => "9", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "MU 301", "quantity" => "5", "product_unit" => "SAK", "note" => "0785 MASJID AL-HIDAYAH", "to_stock" => "3", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "FLEXSEAL FS WALL", "quantity" => "5", "product_unit" => "SAK", "note" => "0785 MASJID AL-HIDAYAH", "to_stock" => "72", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "COMPONE A+", "quantity" => "1", "product_unit" => "SAK", "note" => "0786 PAK DODO", "to_stock" => "62", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "SPANDEX PASIR MERAH", "quantity" => "3", "product_unit" => "LBR", "note" => "0786 PAK DODO", "to_stock" => "27", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "VELLINO 60 / 60 TANBI GIARDINO WHITE", "quantity" => "12", "product_unit" => "DUS", "note" => "0786 PAK DODO", "to_stock" => "50", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "SEMEN TIGA RODA", "quantity" => "10", "product_unit" => "ZAK", "note" => "0791 IING TK KELINCI", "to_stock" => "133", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2712", "product_name" => "TANGGA ALM DP 9", "quantity" => "1", "product_unit" => "BH", "note" => "0795 JUAL", "to_stock" => "2", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "MULTIPLEX 15 MM F", "quantity" => "2", "product_unit" => "LBR", "note" => "0796 DODI ANDRIAN", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "GRC JAYA", "quantity" => "2", "product_unit" => "LBR", "note" => "0796 DODI ANDRIAN", "to_stock" => "89", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "SEMEN PADANG 40 KG", "quantity" => "50", "product_unit" => "ZAK", "note" => "0186 TRIMITRA", "to_stock" => "118", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "COMPONE A+", "quantity" => "2", "product_unit" => "SAK", "note" => "0788 TRIMITRA", "to_stock" => "60", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "HPL 003 G / HITAM", "quantity" => "1", "product_unit" => "LBR", "note" => "0809 ZIA MOTOR", "to_stock" => "6", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "MULTIPLEX 18 MM F", "quantity" => "2", "product_unit" => "LBR", "note" => "0809 ZIA MOTOR", "to_stock" => "14", "transaction_type" => "KELUAR"],
            ["date" => "45556", "transaction_code" => "S 2713", "product_name" => "MU 480 / 25 KG", "quantity" => "4", "product_unit" => "SAK", "note" => "0816 BM MEKANIK", "to_stock" => "8", "transaction_type" => "KELUAR"],
        ];

        $items = [
            ["date" => "45558", "transaction_code" => "M 2714", "product_name" => "ULTRAPROOF 5 KG SKY BLUE", "quantity" => "4", "product_unit" => "GLN", "note" => "MASUK", "to_stock" => "4", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2715", "product_name" => "KNEE POLOS RCD 3'' D", "quantity" => "1", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "1", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2715", "product_name" => "WAVIN D 2'' D", "quantity" => "50", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "171", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2715", "product_name" => "WAVIN AW 1'' AW", "quantity" => "100", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "142", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2715", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "200", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "334", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2716", "product_name" => "ROMAN 30 / 60 EMBOSS CATANIA BRUNO", "quantity" => "10", "product_unit" => "DUS", "note" => "MASUK", "to_stock" => "16", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2716", "product_name" => "WH ARISTON AURES EASY", "quantity" => "1", "product_unit" => "BH", "note" => "MASUK", "to_stock" => "1", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "M 2717", "product_name" => "PARALON LISTRIK ANDARU", "quantity" => "200", "product_unit" => "BTG", "note" => "MASUK", "to_stock" => "200", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "MELBOX 18 MM 2 MUKA", "quantity" => "1", "product_unit" => "LBR", "note" => "0834 JUAL", "to_stock" => "11", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "MULTIPLEX 9 MM", "quantity" => "3", "product_unit" => "LBR", "note" => "0834 JUAL", "to_stock" => "25", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "15", "product_unit" => "ZAK", "note" => "0838 TERUS JAYA", "to_stock" => "170", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "MELAMIN LOTUS PUTIH DNM", "quantity" => "1", "product_unit" => "LBR", "note" => "0851 JUAL", "to_stock" => "17", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "SEMEN HEBEL UNIMIX 40KG", "quantity" => "1", "product_unit" => "SAK", "note" => "0857 ISP", "to_stock" => "12", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "PLASTIK COR -", "quantity" => "10", "product_unit" => "BALL", "note" => "0858 JUAL", "to_stock" => "73", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2718", "product_name" => "GENTONG TERASO PUTIH LR-DL", "quantity" => "1", "product_unit" => "BH", "note" => "0862 JUAL", "to_stock" => "0", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2719", "product_name" => "MULTIPLEX 9 MM", "quantity" => "3", "product_unit" => "LBR", "note" => "RETUR PBL 0006", "to_stock" => "28", "transaction_type" => "MASUK"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "MULTIPLEX 12 MM HP", "quantity" => "1", "product_unit" => "LBR", "note" => "0863 JUAL", "to_stock" => "7", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "WAVIN AW 1/2'' AW", "quantity" => "2", "product_unit" => "BTG", "note" => "0842 DODI ANDRIAN", "to_stock" => "332", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "PAPAN GIPSUM A+", "quantity" => "4", "product_unit" => "LBR", "note" => "0845 KOMINFO", "to_stock" => "160", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "GRC JAYA", "quantity" => "1", "product_unit" => "LBR", "note" => "0841 DODI ANDRIAN", "to_stock" => "88", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "KAYU KASO", "quantity" => "10", "product_unit" => "BTG", "note" => "0841 DODI ANDRIAN", "to_stock" => "110", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "WAVIN D 2 1/2'' D", "quantity" => "4", "product_unit" => "BTG", "note" => "0869 H. MUMU", "to_stock" => "156", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2720", "product_name" => "FLEXSEAL FS GRANITE", "quantity" => "1", "product_unit" => "SAK", "note" => "0871 JUAL", "to_stock" => "8", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "S 2721", "product_name" => "MULTIPLEX 15 MM HP", "quantity" => "1", "product_unit" => "LBR", "note" => "0870 YUSTI ANGKRINGAN", "to_stock" => "18", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "B 0141", "product_name" => "FLEXSEAL FS RENDER", "quantity" => "5", "product_unit" => "SAK", "note" => "BANGUNAN OTISTA", "to_stock" => "45", "transaction_type" => "KELUAR"],
            ["date" => "45558", "transaction_code" => "B 0141", "product_name" => "SEMEN HOLCIM 40KG", "quantity" => "3", "product_unit" => "ZAK", "note" => "BANGUNAN OTISTA", "to_stock" => "167", "transaction_type" => "KELUAR"],
        ];

        foreach ($items as $i => $item) {
            $products = Product::query()
                ->select('products.*')
                ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id');

            $words = explode(' ', $item['product_name']);

            foreach ($words as $word) {
                $products = $products->where(function ($subQuery) use ($word) {
                    // Use REGEXP to match partial words in name, variant, or the concatenated field
                    $subQuery->where('products.name', 'LIKE', "%{$word}%")
                        ->orWhere('products.variant', 'LIKE', "%{$word}%");
                });
            }

            if ($products->count() > 0) {
                $unixTimestamp = ($item['date'] - 25569) * 86400;
                $date = (new \DateTime("@$unixTimestamp"))->format('Y-m-d');
                $product = $products->first();

                $transaction = Transaction::updateOrCreate([
                    'code' => $item['transaction_code'],
                    'date' => $date . ' 00:00:01',
                    'type' => $item['transaction_type'] === 'KELUAR' ? 'out' : 'in',
                ], [
                    'created_at' => Carbon::now(),
                    'created_by' => 3,
                ]);

                $transaction->transaction_products()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'from_stock' => $product->stock,
                    'to_stock' => $item['to_stock'],
                    'note' => $item['note'],
                    'is_verified' => 1,
                    'created_by' => 3,
                    'created_at' => Carbon::now(),
                    'verified_by' => 3,
                    'verified_at' => Carbon::now(),
                ]);
            }
        }

    }
}
