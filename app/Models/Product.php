<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function transaction_products()
    {
        return $this->hasMany(TransactionProduct::class);
    }

    public function getStockAttribute()
    {
        $tp = $this->transaction_products()->where('is_verified', 1)->orderByDesc('id')->first();
        if ($tp) {
            return $tp->to_stock;
        }
        return 0;
    }
}
