<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hourly_rate', 'default_role'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Spatie-роль, привязанная к этой должности.
     * Используется User-observer'ом для синхронизации прав сотрудника.
     */
    public function role(): ?Role
    {
        if (! $this->default_role) {
            return null;
        }

        return Role::where('name', $this->default_role)
            ->where('guard_name', 'web')
            ->first();
    }
}
