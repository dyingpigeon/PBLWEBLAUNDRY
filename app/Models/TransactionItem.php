<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'service_item_id',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}