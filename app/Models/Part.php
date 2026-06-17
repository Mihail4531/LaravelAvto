<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'article', 'name', 'unit', 'price',
        'stock_quantity', 'reserved_quantity', 'min_stock_quantity', 'location', 'active',
        'is_universal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'min_stock_quantity' => 'decimal:2',
        'active' => 'boolean',
        'is_universal' => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(PartMovement::class);
    }

    /**
     * Модели авто, на которые подходит запчасть (применяемость).
     */
    public function carModels()
    {
        return $this->belongsToMany(CarModel::class, 'car_model_part');
    }

    /**
     * Краткая строка применяемости для отображения в списках:
     * «универсальная» / «BMW X5, Toyota Camry» / «применяемость не указана».
     */
    public function applicabilityLabel(): string
    {
        if ($this->is_universal) {
            return 'универсальная';
        }

        $models = $this->carModels->map(
            fn (CarModel $m) => trim(($m->brand?->name ?? '').' '.$m->name)
        );

        return $models->isNotEmpty() ? $models->implode(', ') : 'применяемость не указана';
    }

    /**
     * Подходит ли запчасть для модели авто (мягкий режим).
     * Универсальная — всегда; без заполненной применяемости — не придираемся.
     */
    public function fitsModel(?int $carModelId): bool
    {
        if ($this->is_universal) {
            return true;
        }

        // Применяемость не заполнена — данных нет, не предупреждаем.
        if ($this->carModels()->doesntExist()) {
            return true;
        }

        if (! $carModelId) {
            return true;
        }

        return $this->carModels()->whereKey($carModelId)->exists();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_part')
            ->withPivot('quantity', 'price', 'sum', 'is_issued')
            ->withTimestamps();
    }

    public function getAvailableQuantityAttribute(): float
    {
        return max(0, (float) $this->stock_quantity - (float) $this->reserved_quantity);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock_quantity > 0 && $this->available_quantity <= $this->min_stock_quantity;
    }
}
