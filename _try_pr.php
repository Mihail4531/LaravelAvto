<?php

use App\Models\Order;
use App\Models\Part;
use App\Models\PartRequest;
use App\Models\User;

$mech = User::whereHas('roles', fn ($q) => $q->where('name', 'mechanic'))->first();
$order = Order::query()
    ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS])
    ->whereHas('services', fn ($q) => $q->where('executor_id', $mech->id))
    ->first();
$part = Part::where('active', true)->first();

echo "mech={$mech?->id} order={$order?->id} part={$part?->id}".PHP_EOL;

try {
    $pr = PartRequest::create([
        'order_id' => $order->id,
        'part_id' => $part->id,
        'mechanic_id' => $mech->id,
        'quantity' => 1,
        'status' => PartRequest::STATUS_PENDING,
        'comment' => 'test',
    ]);
    echo 'СОЗДАНО id='.$pr->id.PHP_EOL;
    $pr->delete();
    echo 'удалено (тест откатан)'.PHP_EOL;
} catch (Throwable $e) {
    echo 'ОШИБКА: '.get_class($e).': '.$e->getMessage().PHP_EOL;
}
