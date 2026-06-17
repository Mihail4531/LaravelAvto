<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'car_brand_id', 'car_model_id',
        'vin', 'license_plate', 'year', 'mileage', 'color',
        'fuel_type', 'engine_volume', 'power', 'transmission', 'body_type',
    ];

    protected $casts = [
        'engine_volume' => 'decimal:1',
        'power' => 'integer',
    ];

    /**
     * @return array<string, string>
     */
    public static function fuelTypes(): array
    {
        return [
            'petrol' => 'Бензин',
            'diesel' => 'Дизель',
            'hybrid' => 'Гибрид',
            'electric' => 'Электро',
            'gas' => 'Газ (ГБО)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function transmissions(): array
    {
        return [
            'manual' => 'Механика (МКПП)',
            'automatic' => 'Автомат (АКПП)',
            'robot' => 'Робот',
            'variator' => 'Вариатор (CVT)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function bodyTypes(): array
    {
        return [
            'sedan' => 'Седан',
            'hatchback' => 'Хэтчбек',
            'liftback' => 'Лифтбек',
            'wagon' => 'Универсал',
            'suv' => 'Внедорожник / Кроссовер',
            'coupe' => 'Купе',
            'minivan' => 'Минивэн',
            'pickup' => 'Пикап',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Вспомогательный метод для отображения краткого названия авто
    public function getDisplayNameAttribute()
    {
        $brand = $this->brand?->name ?? '?';
        $model = $this->model?->name ?? '?';
        $plate = $this->license_plate ? " · {$this->license_plate}" : '';
        $vin = $this->vin ? " · VIN {$this->vin}" : '';

        return "{$brand} {$model}{$plate}{$vin}";
    }
}
