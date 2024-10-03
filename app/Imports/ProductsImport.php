<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // skip to 2nd row
        if (strtoupper($row[0]) != 'KATEGORI') {

            // define category. create if doesn't exist
            $category = ProductCategory::where('name', $row[0])->first();
            if (!$category) {
                $category = ProductCategory::create([
                    'name' => $row[0],
                ]);
            }

            // define product by code
            $product = Product::where('name', $row[0])
                ->where('variant', $row[2])
                ->where('code', $row[3])
                ->where('unit', $row[4])
                ->first();

            // import if product doesn't duplicated
            if (!$product) {
                // create product
                return Product::create([
                    'product_category_id' => $category->id,
                    'name'    => $row[1],
                    'variant'    => $row[2],
                    'code'     => $row[3],
                    'unit'    => $row[4],
                    'created_by'    => auth()->user()->id,
                ]);
            }
        }
    }
}
