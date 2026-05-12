<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'starts_at', 'ends_at', 'available'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'available' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
