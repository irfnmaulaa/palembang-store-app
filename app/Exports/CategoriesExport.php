<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoriesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect([
            ['NAMA KATEGORI', 'JUMLAH BARANG']
        ]);

        $categories = ProductCategory::withCount('products')->orderBy('name')->get();

        return $data->merge($categories->map(function ($category, $i) {
            return [ strtoupper($category->name), (string) $category->products_count ];
        }));
    }
}
