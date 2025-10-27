<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'icon',
        'color',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Relationship dengan Service Items
     */
    public function items()
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Relationship dengan Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope active services
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}