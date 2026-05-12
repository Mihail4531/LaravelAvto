<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'article', 'name', 'unit', 'price',
        'stock_quantity', 'reserved_quantity', 'location', 'active'
    ];

    public function getAvailableQuantityAttribute()
    {
        return $this->stock_quantity - $this->reserved_quantity;
    }
}
