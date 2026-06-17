<?php

use App\Models\User;

$u = User::find(3);
if (! $u) {
    echo "Пользователь id=3 не найден\n";

    return;
}

$u->delete();
$fresh = User::withTrashed()->find(3);
echo 'После delete(): trashed='.($fresh->trashed() ? 'да' : 'нет')
    .', deleted_at='.$fresh->deleted_at.PHP_EOL;
echo 'Виден в обычном списке: '.(User::find(3) ? 'да' : 'нет').PHP_EOL;

$fresh->restore();
echo 'После restore(): trashed='.(User::find(3)->trashed() ? 'да' : 'нет').PHP_EOL;
