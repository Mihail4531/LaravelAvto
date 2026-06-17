<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'phone', 'email',
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
        return trim($this->last_name.' '.$this->first_name.' '.$this->middle_name);
    }

    /**
     * Email всегда храним в нижнем регистре без пробелов по краям, чтобы
     * «Ivan@mail.ru» и «ivan@mail.ru» не плодили почти-дубли и совпадали
     * при поиске/конвертации заявок.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value !== null ? mb_strtolower(trim($value)) : null;
    }
}
