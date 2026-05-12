<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кэша прав
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Создаём роли
        $roles = ['admin', 'receptionist', 'mechanic', 'accountant'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Создаём пользователя-админа (если не существует)
        $adminEmail = 'admin@mail.ru';
        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'), // пароль: password
                'active' => true,
            ]
        );

        // Назначаем роль admin
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $this->command->info('Роли и администратор созданы!');
        $this->command->info('Email: admin@mail.ru, Пароль: password');
    }
}
