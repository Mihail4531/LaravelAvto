<?php

use Spatie\Permission\Models\Role;

foreach (['foreman', 'receptionist'] as $r) {
    $role = Role::where('name', $r)->first();
    echo $r
        .': assign='.($role->hasPermissionTo('assign_order_executor') ? 'да' : 'нет')
        .', status='.($role->hasPermissionTo('change_order_status') ? 'да' : 'нет')
        .', update_order='.($role->hasPermissionTo('update_order') ? 'да' : 'нет')
        .PHP_EOL;
}
