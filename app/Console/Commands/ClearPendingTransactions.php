<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\TransactionProduct;
use Illuminate\Console\Command;

class ClearPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:clear-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear pending transactions daily';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (TransactionProduct::where('is_verified', 0)->get() as $transaction_product) {
            $transaction_id = $transaction_product->transaction_id;
            $transaction_product->delete();

            // delete transaction if doesn't have any transaction product
            if (TransactionProduct::where('transaction_id', $transaction_id)->count() <= 0) {
                Transaction::destroy($transaction_id);
            }
        }

        $this->info('Pending transactions cleared successful');
        return 0;
    }
}
