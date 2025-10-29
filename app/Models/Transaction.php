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

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'new' => 'bg-blue-100 text-blue-600',
            'washing' => 'bg-orange-100 text-orange-600',
            'ironing' => 'bg-purple-100 text-purple-600',
            'ready' => 'bg-green-100 text-green-600',
            'picked_up' => 'bg-gray-100 text-gray-600',
            'cancelled' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-600'
        };
    }

    public function getPaymentStatusBadgeClass()
    {
        return match ($this->payment_status) {
            'pending' => 'bg-yellow-100 text-yellow-600',
            'paid' => 'bg-green-100 text-green-600',
            'partial' => 'bg-blue-100 text-blue-600',
            'overpaid' => 'bg-purple-100 text-purple-600',
            default => 'bg-gray-100 text-gray-600'
        };
    }

    public function getStatusText()
    {
        $statusMap = [
            'new' => 'Baru',
            'washing' => 'Dicuci',
            'ironing' => 'Disetrika',
            'ready' => 'Selesai',
            'picked_up' => 'Diambil',
            'cancelled' => 'Dibatalkan'
        ];

        return $statusMap[$this->status] ?? 'Unknown';
    }

    public function getPaymentStatusText()
    {
        $paymentMap = [
            'pending' => 'Belum Bayar',
            'paid' => 'Lunas',
            'partial' => 'DP',
            'overpaid' => 'Kelebihan'
        ];

        return $paymentMap[$this->payment_status] ?? 'Unknown';
    }

    public function getProgressPercentage()
    {
        $statusOrder = ['new', 'washing', 'ironing', 'ready', 'picked_up'];
        $currentIndex = array_search($this->status, $statusOrder);

        if ($currentIndex === false) {
            return 0;
        }

        return (($currentIndex + 1) / count($statusOrder)) * 100;
    }

    public function getCurrentStepText()
    {
        $statusText = [
            'new' => 'Menunggu diproses',
            'washing' => 'Sedang dicuci',
            'ironing' => 'Sedang disetrika',
            'ready' => 'Siap diambil',
            'picked_up' => 'Sudah diambil'
        ];

        return $statusText[$this->status] ?? 'Sedang diproses';
    }
}