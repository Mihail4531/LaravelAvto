<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\BranchScope;

class OrderPrintController extends Controller
{
    /**
     * Уровни топлива — те же подписи, что и в форме приёмки (OrderForm).
     */
    private const FUEL_LEVELS = [
        'empty' => 'Пустой',
        'quarter' => '¼ бака',
        'half' => '½ бака',
        'three_q' => '¾ бака',
        'full' => 'Полный',
    ];

    /**
     * Печатная форма заказ-наряда — клиент получает её на руки.
     */
    public function print(Order $order)
    {
        abort_unless(auth()->user()?->can('view_order'), 403);

        // Уважаем разграничение по филиалам: закреплённый за точкой сотрудник
        // не печатает наряды чужого филиала.
        if (BranchScope::isRestricted() && $order->branch_id !== BranchScope::currentBranchId()) {
            abort(403);
        }

        $order->load(['client', 'car.brand', 'car.model', 'branch', 'receiver', 'services', 'parts', 'customerParts']);

        return view('orders.print', [
            'order' => $order,
            'fuelLevel' => self::FUEL_LEVELS[$order->fuel_level] ?? null,
        ]);
    }
}
