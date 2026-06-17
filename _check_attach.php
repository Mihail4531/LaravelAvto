<?php

use App\Models\Order;
use App\Models\Part;

echo 'Активных запчастей: '.Part::where('active', true)->count().PHP_EOL;
echo 'Всего запчастей: '.Part::count().PHP_EOL;

$order = Order::query()
    ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS])
    ->first();

if ($order) {
    $attached = $order->parts()->pluck('parts.id')->unique();
    echo "Активный заказ №{$order->id}: прикреплено запчастей (distinct) = ".$attached->count().PHP_EOL;
    echo 'Свободных активных для attach = '
        .Part::where('active', true)->whereNotIn('id', $attached)->count().PHP_EOL;
} else {
    echo 'Нет активного заказа для проверки'.PHP_EOL;
}
