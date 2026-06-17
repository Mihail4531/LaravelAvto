<?php

use App\Filament\Resources\AccessControl\RoleResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

function names($q) { return $q->orderBy('name')->pluck('name')->implode(', ') ?: '(пусто)'; }

echo '=== Списки (super_admin должен исчезнуть) ===' . PHP_EOL;
echo 'Исполнитель: ' . names(User::where('active', true)->permission('change_own_service_status')->withoutSuperAdmin()) . PHP_EOL;
echo 'Приёмщик:    ' . names(User::where('active', true)->permission('create_order')->withoutSuperAdmin()) . PHP_EOL;
echo 'Кассир:      ' . names(User::where('active', true)->permission('create_payment')->withoutSuperAdmin()) . PHP_EOL;

echo PHP_EOL . '=== Запрет удаления своей роли ===' . PHP_EOL;
$tmp = User::withoutEvents(fn () => User::create([
    'name' => 'TMP Director', 'email' => 'tmp_dir_test@example.com',
    'password' => bcrypt('x'), 'active' => true,
]));
$tmp->assignRole('director');
Auth::login($tmp->fresh());

$dirRole = Role::where('name', 'director')->first();
$mechRole = Role::where('name', 'mechanic')->first();
echo 'canDelete(СВОЯ роль director): ' . (RoleResource::canDelete($dirRole) ? 'YES — ДЫРА!' : 'no — заблокировано') . PHP_EOL;
echo 'canDelete(чужая роль mechanic): ' . (RoleResource::canDelete($mechRole) ? 'YES — можно' : 'no') . PHP_EOL;

User::withoutEvents(fn () => $tmp->forceDelete());
echo PHP_EOL . 'Временный пользователь удалён.' . PHP_EOL;
