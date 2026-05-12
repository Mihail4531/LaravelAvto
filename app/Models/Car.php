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
        'vin', 'year', 'mileage', 'color'
    ];

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
        return "$brand $model (VIN: {$this->vin})";
    }
}
