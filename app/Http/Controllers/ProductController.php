<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Rules\ProductCategoryIdRule;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // define instance
        $products = Product::query()->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')->select('products.*');

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
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Pertama dibuat', 'order' => 'created_at-asc'],
        ];

        if ($request->ajax()) {
            // return json
            return response()->json([
                'results' => collect($products->items())->map(function ($product) {
                    $latest_tp = $product->transaction_products()->where('is_verified', 1)->orderByDesc('id')->first();
                    return [
                        'id' => json_encode([
                            'id' => $product->id,
                            'name' => $product->name . ' - ' . $product->variant,
                            'code' => $product->code,
                            'stock' => @$latest_tp ? $latest_tp->to_stock : 0,
                        ]),
                        'text' => $product->name . '/' . $product->variant . '/' . $product->code,
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
}
