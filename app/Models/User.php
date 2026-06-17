<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'login',
        'email',
        'password',
        'position_id',
        'branch_id',
        'phone',
        'avatar_path',
        'hire_date',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | СВЯЗИ
    |--------------------------------------------------------------------------
    */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Заявки, которые обработал этот сотрудник
    public function processedAppointments()
    {
        return $this->hasMany(Appointment::class, 'processed_by');
    }

    // Заказы, где сотрудник – приёмщик
    public function receivedOrders()
    {
        return $this->hasMany(Order::class, 'receiver_id');
    }

    // Услуги, где сотрудник – исполнитель (через pivot order_service)
    public function executedServices()
    {
        return $this->belongsToMany(Service::class, 'order_service', 'executor_id')
            ->withPivot('order_id', 'quantity', 'price', 'sum', 'status')
            ->withTimestamps();
    }

    // Платежи, которые принял этот сотрудник
    public function paymentsTaken()
    {
        return $this->hasMany(Payment::class, 'cashier_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Исключает технический аварийный аккаунт super_admin из людских списков
     * (исполнители, приёмщики, кассиры) — это break-glass, а не сотрудник.
     */
    public function scopeWithoutSuperAdmin(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'super_admin'));
    }

    /*
    |--------------------------------------------------------------------------
    | ДОСТУП К ПАНЕЛИ FILAMENT
    |--------------------------------------------------------------------------
    */
    public function canAccessPanel(Panel $panel): bool
    {
        // Доступ к панели — для super_admin и для любой роли,
        // получившей permission 'access_admin_panel'.
        if (! $this->active) {
            return false;
        }

        return $this->hasRole('super_admin') || $this->can('access_admin_panel');
    }

    /**
     * Аватар для шапки панели Filament (HasAvatar).
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }
}
