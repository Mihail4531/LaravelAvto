<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Давальческая запчасть/материал — привезена клиентом.
 * Учётно: не со склада и без стоимости (берём только за работу).
 */
class OrderCustomerPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'name', 'quantity', 'unit', 'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
