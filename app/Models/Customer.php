<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone', 
        'email',
        'address',
        'notes'
    ];

    /**
     * Relationship dengan Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersAttribute()
    {
        return $this->transactions()->count();
    }
}