<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'phone', 'email'
    ];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);
    }
}
