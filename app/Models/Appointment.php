<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'time_slot_id', 'car_brand_id', 'car_model_id',
        'client_name', 'client_phone', 'client_email', 'problem_description',
        'status', 'processed_by', 'processed_at', 'order_id', 'reject_reason',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_REJECTED = 'rejected';

    const STATUS_CONVERTED = 'converted';

    const STATUS_CANCELLED = 'cancelled';

    /**
     * Склейка ФИО из частей в одну строку (как Client::full_name).
     * Лишние пробелы схлопываются, пустое отчество не оставляет хвоста.
     * Используется и мастером записи на сайте, и формой заявки в админке.
     */
    public static function composeName(?string $lastName, ?string $firstName, ?string $middleName): string
    {
        return trim(preg_replace('/\s+/', ' ', "{$lastName} {$firstName} {$middleName}"));
    }

    public static function statuses()
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_CONFIRMED => 'Подтверждена',
            self::STATUS_REJECTED => 'Отклонена',
            self::STATUS_CONVERTED => 'Преобразована в заказ',
            self::STATUS_CANCELLED => 'Отменена',
        ];
    }

    /** Цвет статуса (единый для всех таблиц/карточек). */
    public static function statusColor(?string $state): string
    {
        return [
            self::STATUS_NEW => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_CONVERTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'gray',
        ][$state] ?? 'gray';
    }

    /** Иконка статуса (единая для всех таблиц/карточек). */
    public static function statusIcon(?string $state): string
    {
        return [
            self::STATUS_NEW => 'heroicon-m-bell-alert',
            self::STATUS_CONFIRMED => 'heroicon-m-check-circle',
            self::STATUS_CONVERTED => 'heroicon-m-arrow-right-circle',
            self::STATUS_REJECTED => 'heroicon-m-x-circle',
            self::STATUS_CANCELLED => 'heroicon-m-minus-circle',
        ][$state] ?? 'heroicon-m-question-mark-circle';
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function carBrand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_service');
    }
}
