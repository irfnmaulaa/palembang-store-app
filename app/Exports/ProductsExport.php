<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect([
            ['No', 'Kategori', 'Nama Barang', 'Variant', 'Kode Barang', 'Stok']
        ]);

        $products = Product::query()
            ->select('products.*')
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->orderBy('product_categories.name')
            ->get();

        return $data->merge($products->map(function ($product, $i) {
            $category = '-';
            if ($product->category) {
                $category = $product->category->name;
            }

            return [ $i + 1, $category, $product->name, $product->variant, $product->code, (string) $product->stock ];
        }));
    }
}
