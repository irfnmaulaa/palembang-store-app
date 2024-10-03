<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cookie;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{
    protected $products = [];

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // skip to 2nd row
        if (strtoupper($row[0]) != 'KATEGORI') {

            $category_name = $row[0];
            $name = $row[1];
            $variant = $row[2];
            $code = $row[3];
            $unit = $row[4];

            $product = [
                'category_name' => $category_name,
                'name' => $name,
                'variant' => $variant,
                'code' => $code,
                'unit' => $unit,
                'is_valid' => true,
                'note' => '',
            ];

            // define category. create if doesn't exist
            $category = ProductCategory::where('name', $category_name)->first();
            if (!$category) {
                $product['is_valid'] = false;
                $product['note'] .= 'Kategori tidak ditemukan. ';
            }

            // if product is exist
            $products_count = Product::where('name', $name)
                ->where('variant', $variant)
                ->count();
            if ($products_count > 0) {
                $product['is_valid'] = false;
                $product['note'] .= 'Barang sudah ada. ';
            }

            // if product is duplicated
            $is_product_exist = collect($this->products)->first(function($prod) use ($name, $variant) {
                return $prod['name'] == $name && $prod['variant'] == $variant;
            });
            if ($is_product_exist) {
                $product['is_valid'] = false;
                $product['note'] .= 'Barang duplikat. ';
            }


            // check if field doesn't fill
            foreach (['name', 'variant', 'code', 'unit'] as $key) {
                if (!$product[$key]) {
                    $product['is_valid'] = false;
                    $product['note'] .=  strtoupper($key) . ' tidak diisi. ';
                }
            }

            $this->products[] = $product;
        }
    }

    public function getProducts(): \Illuminate\Support\Collection
    {
        return collect($this->products);
    }
}
