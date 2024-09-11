<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CheckStockController extends Controller
{
    public function index(Request $request)
    {
        // define instance
        $categories = new ProductCategory();

        // searching settings
        if ($request->has('keyword')) {
            $categories = $categories->where('name', 'LIKE', '%' . $request->get('keyword') . '%');
        }

        // order-by settings
        $order = ['name', 'asc'];
        if ($request->has('order')) {
            $order_query = explode('-', $request->get('order'));
            if (count($order_query) >= 2) $order = $order_query;
        }

        // order-by additional settings: with products count
        if ($order[0] === 'products_count') {
            $categories = $categories->withCount('products');
        }

        // order-by statements
        $categories = $categories->orderBy($order[0], $order[1]);

        // final statements
        $categories = $categories
            ->paginate(get_per_page_default())
            ->appends($request->query());

        // define order options for front-end
        $order_options = [
            ['label' => 'Nama A-Z', 'order' => 'name-asc'],
            ['label' => 'Nama A-Z', 'order' => 'name-desc'],
            ['label' => 'Jumlah barang paling banyak', 'order' => 'products_count-desc'],
            ['label' => 'Jumlah barang paling sedikit', 'order' => 'products_count-asc'],
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Pertama dibuat', 'order' => 'created_at-asc'],
        ];

        // return view
        return view('admin.check_stocks.index', compact('categories', 'order_options'));
    }

    // print check stocks
    public function store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['required', 'exists:product_categories,id'],
        ]);

        $products = Product::query()
            ->select('products.*')
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->whereIn('product_category_id', $validated['category_ids'])
            ->orderBy('product_categories.name')
            ->orderBy('products.name')
            ->get();

        return view('admin.check_stocks.print.index', compact('products'));
    }
}
