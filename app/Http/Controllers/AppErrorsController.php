<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CalculationErrorChecker;
use App\Models\RedundantErrorChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppErrorsController extends Controller
{
    public function index(Request $request)
    {
        // define error type
        $errorType = $request->query('error_type', 'redundant');

        // get redundant errors
        $redundant_errors = RedundantErrorChecker::paginate( get_per_page_default() );

        // get calculation errors
        $calculation_errors = CalculationErrorChecker::paginate( get_per_page_default() );

        // define errors
        $errors = [
            'redundant' => [
                'label' => 'Redundant Errors',
                'data'  => $redundant_errors,
            ],
            'calculation' => [
                'label' => 'Calculation Errors',
                'data'  => $calculation_errors,
            ],
        ];

        // return view
        return view('admin.app_errors.index', compact('errorType', 'errors'));
    }

    // check errors
    public function check()
    {
        // truncate error checkers table
        RedundantErrorChecker::truncate();
        CalculationErrorChecker::truncate();

        // check redundant errors
        $this->checking_redundant_errors();

        // check calculation errors
        $last_product_id = 0;
        $this->checking_calculation_errors($last_product_id);

        // response success
        return response()->json([
            'status' => 'success',
        ]);
    }

    // check redundant errors
    public function checking_redundant_errors()
    {
        $duplicates = DB::table('transactions as t')
            ->join('transaction_products as tp', 'tp.transaction_id', '=', 't.id')
            ->leftJoin('products as p', 'tp.product_id', '=', 'p.id')
            ->select(
                't.id as transaction_id',
                't.code as transaction_code',
                't.type as transaction_type',
                't.created_by as transaction_creator',
                'tp.product_id',
                'tp.quantity',
                'tp.note',
                'tp.from_stock',
                'tp.to_stock'
            )
            ->selectRaw('COUNT(*) as duplicate_count')
            ->groupBy(
                'transaction_id',
                'transaction_code',
                'transaction_type',
                'transaction_creator',
                'tp.product_id',
                'tp.quantity',
                'tp.note',
                'tp.from_stock',
                'tp.to_stock',
            )
            ->where('p.deleted_at', null)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            RedundantErrorChecker::create([
                'transaction_id' => $duplicate->transaction_id,
                'product_id' => $duplicate->product_id,
                'quantity' => $duplicate->quantity,
                'note' => $duplicate->note,
                'from_stock' => $duplicate->from_stock,
                'to_stock' => $duplicate->to_stock,
                'duplicate_count' => $duplicate->duplicate_count,
                'created_by' => $duplicate->transaction_creator,
            ]);
        }
    }

    // check calculation errors
    public function checking_calculation_errors($last_product_id)
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
                        CalculationErrorChecker::create([
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

        $this->checking_calculation_errors($last_product_id);
    }
}
