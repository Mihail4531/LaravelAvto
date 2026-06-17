<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'city', 'address', 'phone', 'email',
        'work_hours',           // оставьте для обратной совместимости
        'work_days_start',      // день начала рабочей недели
        'work_days_end',        // день окончания рабочей недели
        'work_time_start',      // время начала работы
        'work_time_end',        // время окончания работы
        'latitude', 'longitude', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'work_time_start' => 'datetime:H:i',
        'work_time_end' => 'datetime:H:i',
    ];

    // Доступ к расписанию в человеко-читаемом виде
    public function getWorkScheduleAttribute(): string
    {
        $days = [
            'monday' => 'Пн', 'tuesday' => 'Вт', 'wednesday' => 'Ср',
            'thursday' => 'Чт', 'friday' => 'Пт', 'saturday' => 'Сб', 'sunday' => 'Вс',
        ];
        $start = $days[$this->work_days_start] ?? $this->work_days_start;
        $end = $days[$this->work_days_end] ?? $this->work_days_end;
        $timeStart = $this->work_time_start?->format('H:i') ?? '09:00';
        $timeEnd = $this->work_time_end?->format('H:i') ?? '21:00';

        return ($start === $end) ? "{$start} {$timeStart}–{$timeEnd}" : "{$start}–{$end} {$timeStart}–{$timeEnd}";
    }

    // Связи (оставляем как есть)
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
