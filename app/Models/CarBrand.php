<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarBrand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'logo', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function models()
    {
        return $this->hasMany(CarModel::class);
    }
}
