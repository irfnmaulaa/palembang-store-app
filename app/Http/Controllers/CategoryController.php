<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin')->except(['index']);
    }

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

        if ($request->ajax()) {
            // return json
            return response()->json([
                'results' => collect($categories->items())->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'text' => $category->name,
                    ];
                })->toArray(),
                'pagination' => [
                    'more' => $categories->hasMorePages(),
                ]
            ]);
        } else {
            // return view
            return view('admin.categories.index', compact('categories', 'order_options'));
        }
    }

    public function create()
    {
        $item = null;
        return view('admin.categories.form', compact('item'));
    }

    public function store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'name' => ['required'],
        ]);

        // store
        ProductCategory::create($validated);

        // return
        return redirect()->route('admin.categories.index')->with('message', 'Kategori berhasil ditambahkan');
    }

    public function show(Request $request, ProductCategory $category)
    {
        $products = $category->products();

        // searching settings
        if ($request->query('keyword')) {
            $products = $products
                ->where(DB::raw("CONCAT(name, ' ', variant)"), 'LIKE', '%' . $request->get('keyword') . '%')
                ->orWhere('code', 'LIKE', '%' . $request->get('keyword') . '%');
        }

        // final statement
        $products = $products
            ->orderBy('name')
            ->paginate(get_per_page_default());

        return view('admin.categories.show', compact('category', 'products'));
    }

    public function edit(ProductCategory $category)
    {
        $item = $category;
        return view('admin.categories.form', compact('item'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        // validation
        $validated = $request->validate([
            'name' => ['required'],
        ]);

        // store
        $category->update($validated);

        // return
        return redirect()->route('admin.categories.show', $category)->with('message', 'Kategori berhasil diperbarui');
    }

    public function destroy(ProductCategory $category)
    {
        // delete
        try {
            if ($category->products()->count() == 0) {
                $category->products()->onlyTrashed()->forceDelete();
            }

            $category->delete();
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Tidak dapat menghapus karna terdapat data dari tabel lain yang berelasi dengan data ini.'
            ], 400);
        }

        // return
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('admin.categories.index')
        ]);
    }

    public function export($type)
    {
        $filename = 'KATEGORI_' . Carbon::now()->format('YmdHis');

        switch ($type) {
            case 'excel':
                return Excel::download(new CategoriesExport, $filename . '.xlsx');
            case 'csv':
                return Excel::download(new CategoriesExport, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return Excel::download(new CategoriesExport, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return "url export salah";
        }
    }

    public function add_product(Request $request, ProductCategory $category)
    {
        // validation
        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
        ]);

        // define product ids
        $product_ids = collect($validated['product_ids'])->map(function ($product) {
            return json_decode($product)->id;
        })->all();

        // add products to category
        Product::whereIn('id', $product_ids)->update([
            'product_category_id' => $category->id,
        ]);

        // return
        return redirect()->back()->with('message', 'Barang berhasil dimasukan');
    }

    public function remove_product(Request $request, ProductCategory $category)
    {
        // validation
        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
        ]);

        // define uncategorized category
        $uncategorized = ProductCategory::where('name', '-')->first();
        if (!$uncategorized) {
            $uncategorized = ProductCategory::create([
                'name' => '-'
            ]);
        }

        // remove product from category
        $category->products()->whereIn('id', $validated['product_ids'])->update([
            'product_category_id' => $uncategorized->id,
        ]);

        // return
        return redirect()->back()->with('message', 'Barang berhasil dikeluarkan');
    }
}
