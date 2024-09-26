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
            ['KATEGORI', 'NAMA BARANG', 'KODE BARANG', 'STOK SAAT INI']
        ]);

        $products = Product::query()
            ->select('products.*')
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->orderBy('product_categories.name')
            ->get();

        return $data->merge($products->map(function ($product) {
            $category = '-';
            if ($product->category) {
                $category = $product->category->name;
            }

            return [ strtoupper($category), strtoupper($product->name . ' ' . $product->variant), strtoupper($product->code), strtoupper((string) $product->stock) . ' ' . strtoupper($product->unit) ];
        }));
    }
}
