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
    'status', 'processed_by', 'processed_at', 'order_id', 'reject_reason'
];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CONVERTED = 'converted';
    const STATUS_CANCELLED = 'cancelled';

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
