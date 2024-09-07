<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Old\Item;
use App\Models\Old\Transaction;
use App\Models\Old\User;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $roles = [
            'BOS' => 'super',
            'GDG' => 'staff',
            'ADM' => 'admin',
            'SRM' => 'staff',
            'EVA' => 'staff',
        ];
        foreach (User::all() as $user) {
            \App\Models\User::create([
                'name' => $user->name,
                'username' => $user->username,
                'password' => $user->password,
                'role' => $roles[$user->identifier],
            ]);
        }

        foreach (\App\Models\Old\Type::all() as $type) {
            \App\Models\ProductCategory::create([
                'id' => $type->id,
                'name' => $type->name,
                'created_at' => $type->created_at,
                'updated_at' => $type->updated_at,
            ]);
        }

        foreach (Item::all() as $item) {
            if (isset($item->type_id)) {
                $type = ProductCategory::find($item->type_id);
                if (!$type) {
                    ProductCategory::create([
                        'id' => $item->type_id,
                        'name' => 'Unknown',
                    ]);
                }
            }

            Product::create([
                'id' => $item->id,
                'product_category_id' => $item->type_id,
                'name' => $item->name,
                'unit' => $item->unit,
                'description' => $item->description,
                'code' => $item->code,
                'variant' => $item->variant,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        foreach (Transaction::where('id', '>', 10433)->take(1000)->orderBy('id')->get() as $transaction) {
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
                            'description' => $product->description,
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
