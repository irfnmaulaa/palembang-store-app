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
            $product = Product::where('code', $row[3])->first();

            // import if product doesn't duplicated
            if (!$product) {

                // create product
                $product = Product::create([
                    'product_category_id' => $category->id,
                    'name'    => $row[1],
                    'variant'    => $row[2],
                    'code'     => $row[3],
                    'unit'    => $row[4],
                    'created_by'    => auth()->user()->id,
                ]);

                // if current stock is filled. create a products-in transaction
                if (!empty($row[5])) {
                    $transaction = Transaction::create([
                        'date' => date('Y-m-d H:i:s'),
                        'type' => 'in',
                        'created_by' => auth()->user()->id,
                        'code' => 'I ' . date('dmyHis'),
                    ]);
                    $transaction->transaction_products()->create([
                        'product_id' => $product->id,
                        'quantity' => $row[5],
                        'from_stock' => 0,
                        'to_stock' => $row[5],
                        'note' => 'IMPORT',
                        'is_verified' => 1,
                        'verified_by' => auth()->user()->id,
                        'created_by' => auth()->user()->id,
                        'verified_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                return $product;
            }
        }
    }
}
