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
        'paid_amount',
        'change_amount',
        'notes',
        'customer_notes',
        'status',
        'payment_status',
        'payment_method',
        'timeline',
        'order_date',
        'estimated_completion',
        'washing_started_at',
        'ironing_started_at',
        'completed_at',
        'picked_up_at',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'estimated_completion' => 'datetime',
        'washing_started_at' => 'datetime',
        'ironing_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'timeline' => 'array'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['washing', 'ironing']);
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'picked_up');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }

    // Helpers
    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid' || $this->payment_status === 'overpaid';
    }

    public function updateTimeline($status, $completed = true)
    {
        $timeline = $this->timeline ?? [];
        $timeline[] = [
            'status' => $status,
            'time' => now()->toISOString(),
            'completed' => $completed
        ];
        $this->timeline = $timeline;
        $this->save();
    }
}