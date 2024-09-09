<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
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
            ->paginate(10)
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
        return redirect()->back()->with('message', 'Kategori berhasil ditambahkan');
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
        return redirect()->back()->with('message', 'Kategori berhasil diperbarui');
    }

    public function destroy(ProductCategory $category)
    {
        // delete
        $category->delete();

        // return
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function export($type)
    {
        $filename = 'categories_' . Carbon::now()->format('YmdHis');

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
}
