<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RedundantErrorChecker;
use Illuminate\Http\Request;

class RedundantErrorCheckerController extends Controller
{
    public function index()
    {
        // define recs
        $recs = RedundantErrorChecker::paginate( get_per_page_default() );

        // return view
        return view('admin.rec.index', compact('recs'));
    }

    // check rec
    public function check()
    {
        // truncate redundant error checkers table
        RedundantErrorChecker::truncate();

        $last_product_id = 0;
        $this->checking($last_product_id);

        // response success
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function checking($last_product_id)
    {
        if (Product::where('id', '>', $last_product_id)->count() == 0) return ;

        // product all
        foreach (Product::where('id', '>', $last_product_id)->take(1000)->get() as $product) {
            $last = null;
            foreach ($product->transaction_products()->select('transaction_products.*')->join('transactions', 'transactions.id', '=', 'transaction_products.transaction_id')->where('is_verified', 1)->orderBy('transactions.date')->get() as $i => $tp) {
                if ($i > 0) {
                    $latest_stock = $last->to_stock;

                    if ($tp->transaction->type === 'in') {
                        $expected_current_stock = $latest_stock + $tp->quantity;
                    } else {
                        $expected_current_stock = $latest_stock - $tp->quantity;
                    }

                    if ($expected_current_stock != $tp->to_stock) {
                        RedundantErrorChecker::create([
                            'product_id' => $product->id,
                            'from_transaction_product_id' => $last->id,
                            'to_transaction_product_id' => $tp->id,
                            'expected_current_stock' => $expected_current_stock,
                            'actual_current_stock' => $tp->to_stock,
                        ]);
                    }

                }
                $last = $tp;
            }
            $last_product_id = $product->id;
        }

        $this->checking($last_product_id);
    }
}
