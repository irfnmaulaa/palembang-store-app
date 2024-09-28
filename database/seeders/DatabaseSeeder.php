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
            'BOS' => 'admin',
            'GDG' => 'staff',
            'ADM' => 'admin',
            'SRM' => 'admin',
            'EVA' => 'admin',
        ];
        foreach (User::all() as $user) {
            \App\Models\User::create([
                'name' => strtoupper($user->identifier),
                'username' => strtoupper($user->username),
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
            if (!empty($item->type_id)) {
                $type = ProductCategory::find($item->type_id);
                if (!$type) {
                    ProductCategory::create([
                        'id' => $item->type_id,
                        'name' => '-',
                    ]);
                }
            } else {
                // define uncategorized category
                $uncategorized = ProductCategory::where('name', '-')->first();
                if (!$uncategorized) {
                    $uncategorized = ProductCategory::create([
                        'name' => '-'
                    ]);
                }
                $item->type_id = $uncategorized->id;
            }

            Product::create([
                'id' => $item->id,
                'product_category_id' => $item->type_id,
                'name' => $item->name,
                'unit' => $item->unit,
                'code' => $item->code,
                'variant' => $item->variant,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }
    }
}
