<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position_id',
        'branch_id',
        'phone',
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
    | ДОСТУП К ПАНЕЛИ FILAMENT
    |--------------------------------------------------------------------------
    */
    public function canAccessPanel(Panel $panel): bool
    {
        // Разрешаем доступ только пользователям с перечисленными ролями
        return $this->hasRole(['admin', 'receptionist', 'mechanic', 'accountant']);
    }
}
