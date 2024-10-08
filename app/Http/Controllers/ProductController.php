<?php

namespace App\Http\Controllers;

use App\Exports\ProductDetailExport;
use App\Exports\ProductsExport;
use App\Exports\UsersExport;
use App\Imports\ProductsImport;
use App\Models\Old\Item;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Rules\ProductCategoryIdRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        // define instance
        $products = Product::query()
            ->select('products.*')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id');

        // searching settings
        if ($request->query('keyword')) {
            // Split the keyword into words
            $words = explode(' ', $request->query('keyword'));

            foreach ($words as $word) {
                $products = $products->where(function ($subQuery) use ($word) {
                    // Use REGEXP to match partial words in name, variant, or the concatenated field
                    $subQuery->where('products.name', 'LIKE', "%{$word}%")
                        ->orWhere('products.variant', 'LIKE', "%{$word}%")
                        ->orWhere('product_categories.name', 'LIKE', '%' . $word . '%');
                });
            }
        }

        // filter by product category
        if ($request->has('product_category_id')) {
            $products = $products->where('products.product_category_id', $request->get('product_category_id'));
        }

        // order-by settings
        $order = ['category', 'asc'];
        if ($request->has('order')) {
            $order_query = explode('-', $request->get('order'));
            if (count($order_query) >= 2) $order = $order_query;
        }

        if ($order[0] == 'category') $order[0] = 'product_categories.name';
        if ($order[0] == 'name') $order[0] = 'products.name';
        if ($order[0] == 'created_at') $order[0] = 'products.created_at';

        // order-by statements
        $products = $products
            ->orderBy($order[0], $order[1]);
        if ($order[1] === 'category') {
            $products = $products
                ->orderBy('name', $order[1]);
        }

        // final statements
        $products = $products
            ->with(['category'])
            ->paginate( get_per_page_default() )
            ->appends($request->query());

        // define order options for front-end
        $order_options = [
            ['label' => 'Nama kategori A-Z', 'order' => 'category-asc'],
            ['label' => 'Nama kategori Z-A', 'order' => 'category-desc'],
            ['label' => 'Nama barang A-Z', 'order' => 'name-asc'],
            ['label' => 'Nama barang Z-A', 'order' => 'name-desc'],
            ['label' => 'Stok paling banyak', 'order' => 'latest_stock-desc'],
            ['label' => 'Stok paling sedikit', 'order' => 'latest_stock-asc'],
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Pertama dibuat', 'order' => 'created_at-asc'],
        ];

        if ($request->ajax()) {
            // response view
            if ($request->query('response') === 'view') {
                return response()->json([
                    'table' => view('admin.products.ajax.table', compact('products', 'order_options'))->render(),
                    'pagination' => view('admin.products.ajax.pagination', compact('products', 'order_options'))->render(),
                    'summary' => view('admin.products.ajax.summary', compact('products', 'order_options'))->render(),
                ]);
            }

            // return json
            return response()->json([
                'results' => collect($products->items())->map(function ($product) {
                    return [
                        'id' => json_encode([
                            'id' => $product->id,
                            'name' => $product->name . ' ' . $product->variant,
                            'code' => $product->code,
                            'stock' => $product->pending_stock,
                            'unit' => $product->unit,
                        ]),
                        'text' => $product->name . ' ' . $product->variant,
                    ];
                })->toArray(),
                'pagination' => [
                    'more' => $products->hasMorePages(),
                ]
            ]);
        } else {
            // return view
            return view('admin.products.index', compact('products', 'order_options'));
        }
    }

    public function show(Request $request, Product $product)
    {
        $start = date('Y-m-d') . ' 00:00:00';
        $end   = date('Y-m-d') . ' 23:59:59';

        // get first transaction
        $first_transaction = $product->transaction_products()
            ->with(['transaction'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->orderBy(DB::raw('DATE(transactions.date)'))
            ->orderBy('transaction_products.id')
            ->first();
        if ($first_transaction) {
            $start = Carbon::parse($first_transaction->transaction->date)->format('Y-m-d') . ' 00:00:00';
        }

        // get last transaction
        $last_transaction = $product->transaction_products()
            ->with(['transaction'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->orderByDesc(DB::raw('DATE(transactions.date)'))
            ->orderByDesc('transaction_products.id')
            ->first();
        if ($last_transaction) {
            $end = Carbon::parse($last_transaction->transaction->date)->format('Y-m-d') . ' 23:59:59';
        }

        if ($request->query('start_date')) {
            $start = $request->query('start_date')  . ' 00:00:00';
        }

        if ($request->query('end_date')) {
            $end = $request->query('end_date') . ' 23:59:59';
        }

        // define instance
        $transaction_products = TransactionProduct::query()
            ->select(['transaction_products.*', 'transactions.*'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_products.product_id', '=', 'products.id')
            ->where('transaction_products.is_verified', 1)
            ->where('transaction_products.product_id', $product->id)
            ->whereBetween('transactions.date', [$start, $end]);

        // searching settings
        if ($request->has('keyword')) {

            // Split the keyword into words
            $words = explode(' ', $request->query('keyword'));

            foreach ($words as $word) {
                $transaction_products = $transaction_products->where(function ($subQuery) use ($word) {
                    // Use REGEXP to match partial words in name, variant, or the concatenated field
                    $subQuery
                        ->orWhere('transactions.code', 'LIKE', '%' . $word . '%')
                        ->orWhere('transaction_products.note', 'LIKE', '%' . $word . '%');
                });
            }

        }

        // order-by settings
        $order = ['transactions.date', 'asc'];
        if ($request->has('order')) {
            $order_query = explode('-', $request->get('order'));
            if (count($order_query) >= 2) $order = $order_query;
        }

        if ($order[0] === 'created_at') {
            $order[0] = 'transaction_products.created_at';
        } elseif ($order[0] === 'code') {
            $order[0] = 'transactions.code';
        } elseif ($order[0] === 'type') {
            $order[0] = 'transactions.type';
        }

        // order-by statements
        if ($order[0] === 'transactions.date') {
            $transaction_products = $transaction_products
                ->orderBy(DB::raw('DATE(transactions.date)'))
                ->orderBy('transaction_products.id');
        } else {
            $transaction_products = $transaction_products->orderBy($order[0], $order[1]);
        }

        // final statements
        $transaction_products = $transaction_products
            ->paginate(get_per_page_default())
            ->appends($request->query());

        // define order options for front-end
        $order_options = [
            ['label' => 'Pertama dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-asc'],
            ['label' => 'Nomor DO A-Z', 'order' => 'code-asc'],
            ['label' => 'Nomor DO Z-A', 'order' => 'code-desc'],
            ['label' => 'Tipe A-Z', 'order' => 'type-asc'],
            ['label' => 'Tipe Z-A', 'order' => 'type-desc'],
        ];

        // clear start date filter
        if ($first_transaction && Carbon::parse($first_transaction->transaction->date)->format('Y-m-d') == Carbon::parse($start)->format('Y-m-d')) {
            $start = null;
        }

        // clear end date filter
        if ($last_transaction && Carbon::parse($last_transaction->transaction->date)->format('Y-m-d') == Carbon::parse($end)->format('Y-m-d')) {
            $end = null;
        }

        // return view
        return view('admin.products.show', compact('product', 'transaction_products', 'order_options', 'start', 'end'));
    }

    public function create()
    {
        $item = null;
        return view('admin.products.form', compact('item'));
    }

    public function store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'name' => ['required'],
            'product_category_id' => ['required', new ProductCategoryIdRule()],
            'variant' => ['required'],
            'code' => ['required', 'nullable'],
            'unit' => ['required', 'nullable'],
        ]);

        // check product category
        $explode = explode('_', $validated['product_category_id']);
        if ($explode[0] === 'new') {
            $category = ProductCategory::create([
                'name' => $explode[1],
            ]);
            $validated['product_category_id'] = $category->id;
        }

        // store
        $request->user()->products_created()->create($validated);

        // return
        return redirect()->route('admin.products.index')->with('message', 'Barang berhasil ditambahkan');
    }

    public function edit(Product $product)
    {
        $item = $product;
        return view('admin.products.form', compact('item'));
    }

    public function update(Request $request, Product $product)
    {
        // validation
        $validated = $request->validate([
            'name' => ['required'],
            'product_category_id' => ['required', new ProductCategoryIdRule()],
            'variant' => ['required'],
            'code' => ['required', 'nullable'],
            'unit' => ['required', 'nullable'],
            'pin' => ['nullable'],
        ]);

        // check pin
        if (empty($validated['pin']) || !Hash::check($validated['pin'], $request->user()->pin)) {
            return redirect()->back()->with('messageError', 'Pin tidak valid');
        }

        // check product category
        $explode = explode('_', $validated['product_category_id']);
        if ($explode[0] === 'new') {
            $category = ProductCategory::create([
                'name' => $explode[1],
            ]);
            $validated['product_category_id'] = $category->id;
        }

        // update
        $product->update(collect($validated)->except(['pin'])->toArray());

        // return
        return redirect()->route('admin.products.show', $product)->with('message', 'Barang berhasil diperbarui');
    }

    public function destroy(Request $request, Product $product)
    {
        // validation
        $validated = $request->validate([
            'pin' => ['nullable'],
        ]);

        // check pin
        if (empty($validated['pin']) || !Hash::check($validated['pin'], $request->user()->pin)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Pin tidak valid',
            ], 400);
        }

        // delete
        try {
            $product->forceDelete();
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Tidak dapat menghapus karna terdapat data dari tabel lain yang berelasi dengan data ini.'
            ], 400);
        }

        // return
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('admin.products.index')
        ]);
    }

    public function export($type)
    {
        $filename = 'BARANG_' . Carbon::now()->format('YmdHis');

        switch ($type) {
            case 'excel':
                return Excel::download(new ProductsExport, $filename . '.xlsx');
            case 'csv':
                return Excel::download(new ProductsExport, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return Excel::download(new ProductsExport, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return "url export salah";
        }
    }

    public function export_detail(Request $request, Product $product, $type)
    {
        if ($request->has('date_range') && $request->query('date_range') != '') {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0] . ' 00:00:00';
            $end = $explode[1] . ' 23:59:59';
        } else {
            $start = $product->created_at->format('Y-m-d H:i:s');
            $end = date('Y-m-d') . ' 23:59:59';
        }

        $filename = str_replace('/', '-', $product->name) . ' ' . str_replace('/', '-', $product->variant) . '_' . Carbon::now()->format('YmdHis');

        switch ($type) {
            case 'excel':
                return (new ProductDetailExport($product, $start, $end))->download($filename . '.xlsx');
            case 'csv':
                return (new ProductDetailExport($product, $start, $end))->download($filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return (new ProductDetailExport($product, $start, $end))->download($filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return "url export salah";
        }
    }

    public function import_preview(Request $request)
    {
        // validation
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        $products = $import->getProducts();
        $products_valid_count = $products->filter(function ($product) { return $product['is_valid']; })->count();

        return view('admin.products.import_preview', compact('products', 'products_valid_count'));
    }

    public function import(Request $request)
    {
        // validation
        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*.category_name' => ['required', 'exists:product_categories,name'],
            'products.*.name' => ['required'],
            'products.*.variant' => ['required'],
            'products.*.code' => ['required'],
            'products.*.unit' => ['required'],
        ]);

        foreach ($validated['products'] as $product) {
            $category = ProductCategory::where('name', $product['category_name'])->first();

            $category->products()->create([
               'name' => $product['name'],
               'variant' => $product['variant'],
               'code' => $product['code'],
               'unit' => $product['unit'],
            ]);
        }

        return redirect()->route('admin.products.index')->with('message', count($validated['products']) . ' barang berhasil ditambahkan');
    }

    public function get_latest_stock_by_date(Request $request)
    {
        // validation
        $validated = $request->validate([
            'product_ids'   => ['required', 'array'],
            'product_ids.*' => ['required', 'numeric', 'exists:products,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        // get products
        $products = Product::whereIn('id', $validated['product_ids'])->get();

        // define date
        $date = $validated['date'];

        // get pending stock by date
        return [
            'date' => $date,
            'products' => $products->map(function ($product) use ($date) {
                return [
                    'product_id' => $product->id,
                    'stock' => $product->get_pending_stock_by_date($date),
                ];
            }),
        ];
    }
}
