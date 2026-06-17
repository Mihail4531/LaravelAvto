<?php

use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;

$role = Role::where('name', 'mechanic')->first();
echo 'mechanic perms part_request: '
    .($role->hasPermissionTo('view_any_part_request') ? 'view_any ' : '')
    .($role->hasPermissionTo('create_part_request') ? 'create ' : '')
    .PHP_EOL;

$mechs = User::whereHas('roles', fn ($q) => $q->where('name', 'mechanic'))->get();
echo 'Механиков: '.$mechs->count().PHP_EOL;

foreach ($mechs as $u) {
    $orders = Order::query()
        ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS])
        ->whereHas('services', fn ($q) => $q->where('executor_id', $u->id))
        ->count();
    echo "  #{$u->id} {$u->name} | active={$u->active} | can create_part_request="
        .($u->can('create_part_request') ? 'да' : 'НЕТ')
        ." | заказов для заявки={$orders}".PHP_EOL;
}
