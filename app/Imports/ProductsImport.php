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
        if ($row[0] != 'KATEGORI') {
            $category = ProductCategory::where('name', $row[0])->first();
            if (!$category) {
                $category = ProductCategory::create([
                    'name' => $row[0],
                ]);
            }

            $product = Product::create([
                'product_category_id' => $category->id,
                'name'    => $row[1],
                'variant'    => $row[2],
                'code'     => $row[3],
                'unit'    => $row[4],
                'created_by'    => auth()->user()->id,
            ]);

            if (!empty($row[5])) {
                $transaction = Transaction::create([
                    'date' => date('Y-m-d H:i:s'),
                    'type' => 'in',
                    'created_by' => auth()->user()->id,
                    'code' => 'I ' . date('dmy'),
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
