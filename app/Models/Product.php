<?php

namespace App\Models;

use App\Models\Old\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        $tp = $this->transaction_products()
            ->select(['transaction_products.*'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->where('is_verified', 1)
            ->orderByDesc(DB::raw('DATE(transactions.date)'))
            ->orderByDesc('transaction_products.id')
            ->first();

        if ($tp) {
            return $tp->to_stock;
        }
        return 0;
    }

    public function getPendingStockAttribute()
    {
        $date = null;
        if (request()->query('date')) {
            $date = request()->query('date');
        }

        return $this->get_pending_stock_by_date($date);
    }

    public function get_pending_stock_by_date($date = null)
    {
        $tp = $this->transaction_products()
            ->select(['transaction_products.*'])
            ->join('transactions', 'transaction_products.transaction_id', '=', 'transactions.id');

        if ($date) {
            $tp = $tp->whereDate('transactions.date', '<=', $date);
        }

        $tp = $tp
            ->orderByDesc(DB::raw('DATE(transactions.date)'))
            ->orderByDesc('transaction_products.id')
            ->first();

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

    public function calculation_errors()
    {
        return $this->hasMany(CalculationErrorChecker::class);
    }
}
