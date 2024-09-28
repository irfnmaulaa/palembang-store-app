<?php

namespace Database\Seeders;

use App\Models\Old\Item;
use App\Models\Old\Transaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TransactionProduct;
use App\Models\User;
use Carbon\Carbon;
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

        $from_stock = 0;
        $count = Item::count();
        foreach (Item::all() as $i => $item) {
            echo "Migrating: " . (round($i / $count * 100)) . "%\n";
            $product = Product::find($item->id);
            $histories = json_decode($item->histories);
            if (is_array($histories)) {
                foreach ($histories as $j => $history) {
                    $transaction = \App\Models\Transaction::where('code', $history->invoice)->whereDate('date', Carbon::parse($history->created_at)->format('Y-m-d'))->first();
                    $created_by = User::where('name', $history->identifier)->first();
                    $created_at = Carbon::parse('2024-09-26 12:00:00')->addSeconds($j)->format('Y-m-d H:i:s');

                    if (!$transaction) {
                        $transaction = \App\Models\Transaction::create([
                            'code' => $history->invoice,
                            'date' => Carbon::parse($history->created_at)->format('Y-m-d H:i:s'),
                            'type' => !empty($history->out) ? 'out' : 'in',
                            'created_by' => !empty($created_by) ? $created_by->id : 1,
                            'created_at' => $created_at,
                        ]);
                    }

                    $transaction->transaction_products()->create([
                        'product_id' => $product->id,
                        'quantity' => !empty($history->out) ? $history->out : $history->in,
                        'from_stock' => $from_stock,
                        'to_stock' => $history->remaining,
                        'note' => $history->description,
                        'is_verified' => 1,
                        'verified_by' => 1,
                        'verified_at' => Carbon::parse($history->created_at)->format('Y-m-d H:i:s'),
                        'created_by' => !empty($created_by) ? $created_by->id : 1,
                        'created_at' => $created_at,
                    ]);
                }
            }
        }

        TransactionProduct::destroy(11666);

        return;

        // 0
        // 3000
        // 6043
        // 9083
        // 10433
        foreach ([0, 3000, 6043, 9083] as $skip) {
            foreach (Transaction::where('id', '>', $skip)->take(3000)->orderBy('id')->get() as $transaction) {
                $items = json_decode(json_decode($transaction->items));
                if (!empty($items->data) && is_array($items->data) && count($items->data) > 0) {
                    $type = $items->data[0]->method;

                    $new_transaction = \App\Models\Transaction::create([
                        'id' => $transaction->id,
                        'code' => $transaction->invoice,
                        'date' => $transaction->updated_at,
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

                            if (!empty($product->type_id)) {
                                $type = ProductCategory::find($product->type_id);
                                if ($type) {
                                    $product->type_id = $type->id;
                                } else {
                                    $product->type_id = null;
                                }
                            }

                            if(empty($product->type_id)){
                                $uncategorized = ProductCategory::where('name', '-')->first();
                                if (!$uncategorized) {
                                    $uncategorized = ProductCategory::create([
                                        'name' => '-'
                                    ]);
                                }
                                $product->type_id = $uncategorized->id;
                            }

                            Product::create([
                                'id' => $product->id,
                                'product_category_id' => isset($product->type_id) ? $product->type_id : null,
                                'deleted_at' => Carbon::now(),
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
}
