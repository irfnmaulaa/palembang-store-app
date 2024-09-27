<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, TransactionProduct::class)->withPivot([
            'id',
            'quantity',
            'from_stock',
            'to_stock',
            'note',
            'is_verified',
            'verified_by',
            'created_by',
            'updated_by',
        ])->orderByPivot('id');
    }

    public function transaction_products()
    {
        return $this->hasMany(TransactionProduct::class)->orderBy('id');
    }
}
