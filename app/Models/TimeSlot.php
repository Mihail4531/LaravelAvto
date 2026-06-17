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

    // ── Scopes ──────────────────────────────────────────────────────────────
    // Единый источник правды «когда слот можно забронировать». Используется
    // и публичным мастером (BookingWizard), и админкой (AppointmentForm).

    /** Свободен (ещё не занят заявкой). */
    public function scopeAvailable($query)
    {
        return $query->where('available', true);
    }

    /** Время ещё не наступило. */
    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    /** Доступен для записи: свободен И в будущем. */
    public function scopeBookable($query)
    {
        return $query->available()->upcoming();
    }
}
