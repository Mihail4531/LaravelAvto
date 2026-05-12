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
        'problem_description', 'status', 'total_amount', 'comment'
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

    public static function statuses()
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Выполнен (ожидает оплаты)',
            self::STATUS_CLOSED => 'Закрыт',
            self::STATUS_CANCELLED => 'Отменён',
        ];
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

   public function recalculateTotal()
{
    $servicesSum = $this->services->sum('pivot.sum');
    $partsSum = $this->parts->sum('pivot.sum');
    $this->total_amount = $servicesSum + $partsSum;
    $this->saveQuietly();
}
}
