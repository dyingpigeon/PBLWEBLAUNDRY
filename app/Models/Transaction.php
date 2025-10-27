<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'customer_id',
        'service_id',
        'total_amount',
        'notes',
        'status',
        'order_date',
        'estimated_completion',
        'completed_at',
        'picked_up_at'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'estimated_completion' => 'datetime',
        'completed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Relationship dengan Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship dengan Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship dengan Transaction Items
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Scope untuk transaksi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'picked_up' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status text untuk UI
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'picked_up' => 'Sudah Diambil',
            'cancelled' => 'Dibatalkan',
            default => 'Unknown'
        };
    }
}