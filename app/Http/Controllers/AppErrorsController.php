<?php

namespace App\Http\Controllers;

use App\Models\CheckingErrorHistory;
use App\Models\Product;
use App\Models\CalculationErrorChecker;
use App\Models\RedundantErrorChecker;
use App\Models\TransactionProduct;
use Carbon\Carbon;
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

        // delete older history
        if (CheckingErrorHistory::count() >= 10) {
            CheckingErrorHistory::truncate();
        }

        // save check error history
        CheckingErrorHistory::create([
            'checked_by' => auth()->user() ? auth()->user()->id : null,
        ]);

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
            )
            ->where('p.deleted_at', null)
            ->whereDate('t.date', '>=', get_app_released_date())
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            RedundantErrorChecker::create([
                'transaction_id' => $duplicate->transaction_id,
                'product_id' => $duplicate->product_id,
                'quantity' => $duplicate->quantity,
                'note' => $duplicate->note,
                'from_stock' => 0,
                'to_stock' => 0,
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
            $transaction_products = $product
                ->transaction_products()
                ->select('transaction_products.*')
                ->join('transactions', 'transactions.id', '=', 'transaction_products.transaction_id')
                ->where('is_verified', 1)
                ->whereDate('transactions.date', '>=', get_app_released_date())
                ->orderBy('transactions.date')
                ->orderBy('transaction_products.id')
                ->get();

            foreach ($transaction_products as $i => $tp) {
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

    // solve errors
    public function solve(Request $request, $type)
    {
        switch ($type) {
            case 'redundant':
                return $this->solve_redundant($request);
            case 'calculation':
                return $this->solve_calculation($request);
            default:
                abort(404);
        }
    }

    // solve redundant errors
    public function solve_redundant(Request $request)
    {
        // validation
        $validated = $request->validate([
            'rec_ids' => ['required', 'array'],
            'rec_ids.*' => ['required', 'exists:redundant_error_checkers,id'],
        ]);

        // get all recs
        $recs = RedundantErrorChecker::whereIn('id', $validated['rec_ids'])->get();

        foreach ($recs as $rec) {

            // get all duplicated transaction products
            $transaction_products = TransactionProduct::query()
                ->where('transaction_id', $rec->transaction_id)
                ->where('product_id', $rec->product_id)
                ->where('note', $rec->note)
                ->where('quantity', $rec->quantity)
                ->get();

            // check count of duplicated transaction products same as expected
            if ($transaction_products->count() == $rec->duplicate_count) {
                // delete rec
                $rec->delete();

                // get first data of duplicated transaction product
                $first = $transaction_products->first();

                // define transaction product ids to remove
                $tp_ids = $transaction_products->pluck('id')->filter(function ($id) use ($first) {
                    return $id != $first->id;
                })->values()->toArray();

                // remove calculation
                CalculationErrorChecker::whereIn('from_transaction_product_id', $tp_ids)
                    ->orWhereIn('to_transaction_product_id', $tp_ids)
                    ->delete();

                // remove duplicate
                TransactionProduct::whereIn('id', $tp_ids)->delete();
            }
        }

        return redirect()->back()->with('message', 'Error solved successful');
    }

    // solve calculation errors
    public function solve_calculation(Request $request)
    {
        // validation
        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['required', 'exists:products,id'],
        ]);

        // get all products has calculation errors
        $products = Product::whereIn('id', $validated['product_ids'])->get();

        foreach ($products as $product) {
            // get first transaction product error
            $first_transaction_product = $product->calculation_errors()->orderBy('id')->first()->from_transaction_product;

            // get all transaction products after first transaction product error
            $transaction_products = $product->transaction_products()
                ->select(['transaction_products.*'])
                ->with(['transaction'])
                ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.date', '>=', Carbon::parse($first_transaction_product->transaction->date)->format('Y-m-d'))
//                ->where('transaction_products.created_at', '>', $first_transaction_product->created_at)
                ->orderBy('transactions.date')
                ->orderBy('transaction_products.id')
                ->get();

            foreach ($transaction_products as $i => $transaction_product) {

                // define type of transaction (in/out)
                $type = $transaction_product->transaction->type;

                // start from first error
                if ($i > 0) {
                    // from_stock is to_stock/remaining of transaction product before
                    $from_stock = $transaction_products[$i-1]->to_stock;

                    // calculate expected to_stock/remaining
                    $to_stock   = $from_stock + $transaction_product->quantity;
                    if ($type === 'out') {
                        $to_stock = $from_stock - $transaction_product->quantity;
                    }

                    // solve
                    $transaction_product->update([
                        'from_stock' => $from_stock,
                        'to_stock' => $to_stock,
                    ]);
                }

            }

            // delete calculation errors of the product
            $product->calculation_errors()->delete();
        }

        return redirect()->back()->with('message', 'Error solved successful');
    }

}
