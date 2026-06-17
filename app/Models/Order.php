<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'client_id', 'car_id', 'receiver_id',
        'planned_finish', 'actual_finish', 'current_mileage',
        'damages_on_acceptance', 'equipment', 'fuel_level',
        'problem_description', 'status', 'total_amount', 'comment',
    ];

    protected $casts = [
        'planned_finish' => 'datetime',
        'actual_finish' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    const STATUS_NEW = 'new';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CLOSED = 'closed';

    const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Выполнен (ожидает оплаты)',
            self::STATUS_CLOSED => 'Закрыт',
            self::STATUS_CANCELLED => 'Отменён',
        ];
    }

    /**
     * Наряд «открыт» (можно менять состав: услуги, запчасти, исполнителей),
     * только пока он новый или в работе. Выполнен/закрыт/отменён — заморожен.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_IN_PROGRESS], true);
    }

    protected static function booted(): void
    {
        // При переводе заказа в статус «Отменён» — снимаем резервы с невыданных запчастей
        static::updating(function (Order $order) {
            if (
                $order->isDirty('status') &&
                $order->status === self::STATUS_CANCELLED &&
                $order->getOriginal('status') !== self::STATUS_CANCELLED
            ) {
                $order->load('parts');

                foreach ($order->parts as $part) {
                    $isIssued = (bool) $part->pivot->is_issued;
                    $qty = (float) $part->pivot->quantity;

                    if (! $isIssued && $qty > 0) {
                        $part->decrement('reserved_quantity', $qty);

                        PartMovement::create([
                            'part_id' => $part->id,
                            'order_id' => $order->id,
                            'user_id' => auth()->id(),
                            'type' => PartMovement::TYPE_RELEASE,
                            'quantity' => $qty,
                            'comment' => 'Снятие резерва при отмене заказа',
                        ]);
                    }
                }
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_service')
            ->withPivot('executor_id', 'quantity', 'price', 'sum', 'status')
            ->withTimestamps();
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'order_part')
            ->withPivot('quantity', 'price', 'sum', 'is_issued')
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Давальческие запчасти — привезённые клиентом. Не со склада и без
     * стоимости (в total_amount не входят), фиксируются для наряда.
     */
    public function customerParts()
    {
        return $this->hasMany(OrderCustomerPart::class);
    }

    public function recalculateTotal(): void
    {
        $this->load('services', 'parts');
        $servicesSum = $this->services->sum('pivot.sum');
        $partsSum = $this->parts->sum('pivot.sum');
        $this->total_amount = $servicesSum + $partsSum;
        $this->saveQuietly();
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->paid_amount);
    }
}
