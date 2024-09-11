<?php

namespace Database\Seeders;

use App\Models\Old\Transaction;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Transaction::where('id', '>', 3000)->take(3000)->orderBy('id')->get() as $transaction) {
            $items = json_decode(json_decode($transaction->items));
            if (!empty($items->data) && is_array($items->data) && count($items->data) > 0) {
                $type = $items->data[0]->method;

                $new_transaction = \App\Models\Transaction::create([
                    'id' => $transaction->id,
                    'code' => $transaction->invoice,
                    'date' => $transaction->created_at,
                    'type' => $type,
                    'created_by' => $transaction->user_id,
                    'created_at' => $transaction->created_at,
                    'updated_by' => 3,
                    'updated_at' => $transaction->updated_at,
                ]);

                foreach ($items->data as $item) {
                    $product = Product::find($item->item->id);
                    if (!$product) {
                        $product = $item->item;

                        if (isset($product->type_id)) {
                            $type = ProductCategory::find($product->type_id);
                            if (!$type) {
                                ProductCategory::create([
                                    'id' => $product->type_id,
                                    'name' => 'Unknown',
                                ]);
                            }
                        }

                        Product::create([
                            'id' => $product->id,
                            'product_category_id' => isset($product->type_id) ? $product->type_id : null,
                            'name' => $product->name,
                            'unit' => $product->unit,
                            'code' => $product->code,
                            'variant' => $product->variant,
                            'created_at' => $product->created_at,
                            'updated_at' => $product->updated_at,
                        ]);
                    }

                    $new_transaction->transaction_products()->create([
                        'product_id' => $item->item->id,
                        'quantity' => $item->quantity,
                        'from_stock' => $item->item->stock,
                        'to_stock' => $item->remaining,
                        'is_verified' => $item->status,
                        'note' => $item->description,
                        'created_by' => $transaction->user_id,
                        'verified_by' => 3,
                        'updated_by' => 3,
                    ]);
                }
            } else {
                echo 'Transaction ID: ' . $transaction->id . "\n";
            }
        }
    }
}
