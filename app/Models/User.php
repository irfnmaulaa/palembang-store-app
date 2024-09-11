<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function products_created()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function transactions_created()
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function getRoleLabelAttribute()
    {
        switch ($this->role) {
            case 'admin':
                return 'Administrator';
            default:
                return 'User';
        }
    }

    public function getRoleDisplayAttribute()
    {
        switch ($this->role) {
            case 'admin':
                return '<span class="badge bg-warning">' . $this->role_label . '</span>';
            default:
                return '<span class="badge bg-primary">' . $this->role_label . '</span>';
        }
    }


    public function getStatusDisplayAttribute()
    {
        if ($this->is_active) {
            return '<span class="badge bg-success badge-lg">' . 'Aktif' . '</span>';
        }
        return '<span class="badge bg-secondary badge-lg">' . 'Nonaktif' . '</span>';
    }
}
