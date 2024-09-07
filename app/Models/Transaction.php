<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, TransactionProduct::class)->withPivot([
            'quantity',
            'from_stock',
            'to_stock',
            'note',
            'is_verified',
            'verified_by',
            'created_by',
            'updated_by',
        ]);
    }

    public function transaction_products()
    {
        return $this->hasMany(TransactionProduct::class);
    }
}
