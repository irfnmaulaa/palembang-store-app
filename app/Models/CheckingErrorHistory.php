<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckingErrorHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function checked_by_user()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
