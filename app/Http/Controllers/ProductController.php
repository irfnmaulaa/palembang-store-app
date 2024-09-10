<?php

namespace App\Http\Controllers;

use App\Exports\ProductDetailExport;
use App\Exports\ProductsExport;
use App\Exports\UsersExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Rules\ProductCategoryIdRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // define instance
        $products = Product::query()
            ->select('products.*')
            ->addSelect([
                'latest_stock' => function($query) {
                    $query->select('to_stock')
                        ->from('transaction_products')
                        ->whereColumn('transaction_products.product_id', 'products.id')
                        ->orderByDesc('id')
                        ->limit(1);
                }
            ])
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id');

        // searching settings
        if ($request->has('keyword')) {
            $products = $products
                ->where('products.name', 'LIKE', '%' . $request->get('keyword') . '%')
                ->orWhere('products.code', 'LIKE', '%' . $request->get('keyword') . '%')
                ->orWhere('product_categories.name', 'LIKE', '%' . $request->get('keyword') . '%');
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
        $products = $products->orderBy($order[0], $order[1]);

        // final statements
        $products = $products
            ->with(['category'])
            ->paginate(10)
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
            // return json
            return response()->json([
                'results' => collect($products->items())->map(function ($product) {
                    return [
                        'id' => json_encode([
                            'id' => $product->id,
                            'name' => $product->name . ' - ' . $product->variant,
                            'code' => $product->code,
                            'stock' => $product->stock,
                        ]),
                        'text' => $product->name . ' / ' . $product->variant . ' / ' . $product->code,
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
        $start = $product->created_at->format('Y-m-d H:i:s');
        $end = date('Y-m-d') . ' 23:59:59';

        if ($request->has('date_range')) {
            $explode = explode(' - ', $request->query('date_range'));
            $start = $explode[0] . ' 00:00:00';
            $end = $explode[1] . ' 23:59:59';
        }

        // define instance
        $transaction_products = TransactionProduct::query()
            ->select(['transaction_products.*', 'transactions.*'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->where('transaction_products.is_verified', 1)
            ->where('transaction_products.product_id', $product->id)
            ->whereBetween('transactions.date', [$start, $end]);

        // searching settings
        if ($request->has('keyword')) {
            $transaction_products = $transaction_products
                ->where(function ($query) use ($request) {
                    $query->where('transaction_products.code', 'LIKE', '%' . $request->get('keyword') . '%');
                });

        }

        // order-by settings
        $order = ['transactions.date', 'desc'];
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
        $transaction_products = $transaction_products->orderBy($order[0], $order[1]);

        // final statements
        $transaction_products = $transaction_products
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
            'code' => ['nullable', 'unique:products,code'],
            'unit' => ['nullable'],
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
        return redirect()->back()->with('message', 'Barang berhasil ditambahkan');
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
            'code' => ['nullable', 'unique:products,code,' . $product->id],
            'unit' => ['nullable'],
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
        $product->update($validated);

        // return
        return redirect()->back()->with('message', 'Barang berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        // delete
        $product->delete();

        // return
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function export($type)
    {
        $filename = 'products_' . Carbon::now()->format('YmdHis');

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

        $filename = 'product_'. str_replace(' ', '-', $product->name) .'_' . str_replace(' ', '-', $product->variant) . '_' . str_replace(' ', '-', $product->code) . '_' . Carbon::now()->format('YmdHis');

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

    public function import(Request $request)
    {
        // validation
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);

        Excel::import(new ProductsImport, $request->file('file'));

        return redirect()->back()->with('message', 'Impor data barang berhasil');
    }
}
