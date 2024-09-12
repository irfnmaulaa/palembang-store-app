<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedundantErrorChecker extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $with = ['from_transaction_product', 'to_transaction_product'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function from_transaction_product()
    {
        return $this->belongsTo(TransactionProduct::class, 'from_transaction_product_id');
    }

    public function to_transaction_product()
    {
        return $this->belongsTo(TransactionProduct::class, 'to_transaction_product_id');
    }
}
