<?php

namespace App\Models;

use App\Models\Old\Item;
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

    public function getPendingStockAttribute()
    {
        $tp = $this->transaction_products()->orderByDesc('id')->first();
        if ($tp) {
            return $tp->to_stock;
        }
        return 0;
    }

    public function scopeWithTrashed($query)
    {
        return $query->withoutGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope');
    }

    public function getStockAtOldAppAttribute()
    {
        $item = Item::find($this->id);
        if ($item) {
            $histories = json_decode($item->histories);
            if (is_array($histories)) {
                return $histories[count($histories) - 1]->remaining;
            }
        }
        return 0;
    }

    public function getIsMatchedStockAttribute()
    {
        return $this->stock_at_old_app == $this->stock;
    }
}
