<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PartRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'part_id', 'mechanic_id',
        'quantity', 'status', 'comment', 'issued_by', 'issued_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_ISSUED = 'issued';

    const STATUS_REJECTED = 'rejected';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает выдачи',
            self::STATUS_ISSUED => 'Выдана',
            self::STATUS_REJECTED => 'Отклонена',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Кладовщик выдаёт запчасть: списывает со склада, добавляет в заказ-наряд
     * как выданную позицию и фиксирует движение склада. Всё в одной транзакции.
     */
    public function fulfill(int $issuerId): void
    {
        DB::transaction(function () use ($issuerId) {
            $part = Part::lockForUpdate()->findOrFail($this->part_id);
            $qty = (float) $this->quantity;

            if ($qty > $part->available_quantity) {
                throw new \RuntimeException(
                    "Недостаточно на складе. Доступно: {$part->available_quantity} {$part->unit}."
                );
            }

            $order = Order::findOrFail($this->order_id);

            if (! in_array($order->status, [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS], true)) {
                throw new \RuntimeException('Наряд уже завершён или закрыт — выдача по заявке невозможна.');
            }

            // Добавляем в заказ как уже выданную позицию (биллинг по цене склада)
            $order->parts()->attach($part->id, [
                'quantity' => $qty,
                'price' => $part->price,
                'sum' => round($qty * (float) $part->price, 2),
                'is_issued' => true,
            ]);

            // Списываем физический остаток
            $part->decrement('stock_quantity', $qty);

            PartMovement::create([
                'part_id' => $part->id,
                'order_id' => $order->id,
                'user_id' => $issuerId,
                'type' => PartMovement::TYPE_ISSUE,
                'quantity' => $qty,
                'comment' => 'Выдача по заявке механика №'.$this->id.' (заказ №'.$order->id.')',
            ]);

            $order->recalculateTotal();

            $this->update([
                'status' => self::STATUS_ISSUED,
                'issued_by' => $issuerId,
                'issued_at' => now(),
            ]);
        });
    }
}
