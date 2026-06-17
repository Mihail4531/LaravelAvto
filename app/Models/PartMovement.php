<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartMovement extends Model
{
    protected $fillable = [
        'part_id', 'order_id', 'user_id', 'type', 'quantity', 'comment',
    ];

    const TYPE_RESERVE = 'reserve';

    const TYPE_RELEASE = 'release';

    const TYPE_ISSUE = 'issue';

    const TYPE_ISSUE_UNDO = 'issue_undo';

    const TYPE_INTAKE = 'intake';

    public static function types(): array
    {
        return [
            self::TYPE_RESERVE => 'Резервирование',
            self::TYPE_RELEASE => 'Снятие резерва',
            self::TYPE_ISSUE => 'Выдача',
            self::TYPE_ISSUE_UNDO => 'Отмена выдачи',
            self::TYPE_INTAKE => 'Поступление',
        ];
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
