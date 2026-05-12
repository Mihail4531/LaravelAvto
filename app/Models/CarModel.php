<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;   // ← добавить
use Illuminate\Support\Str;                     // опционально, для генерации slug

class CarModel extends Model
{
    use HasFactory, SoftDeletes;                 // ← добавить SoftDeletes

    protected $fillable = [
        'car_brand_id',
        'name',
        'slug',          // ← добавить slug
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'deleted_at' => 'datetime', // не обязательно, но для порядка
    ];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    // Опционально: автоматическая генерация slug при создании/обновлении
   
}
