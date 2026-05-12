<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $newAppointments = Appointment::where('status', 'new')->count();
        $activeOrders    = Order::whereIn('status', ['new', 'in_progress'])->count();
        $overdueOrders   = Order::whereIn('status', ['new', 'in_progress'])
            ->whereNotNull('planned_finish')
            ->where('planned_finish', '<', now())
            ->count();

        $todayRevenue = Payment::whereDate('paid_at', today())->sum('amount');
        $monthRevenue = Payment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Новые заявки', $newAppointments)
                ->description('Ожидают обработки')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($newAppointments > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.appointments.index')),

            Stat::make('Активные заказы', $activeOrders)
                ->description($overdueOrders > 0 ? "{$overdueOrders} просрочен(о)" : 'Всё в срок')
                ->descriptionIcon($overdueOrders > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueOrders > 0 ? 'danger' : 'primary')
                ->url(route('filament.admin.resources.orders.index')),

            Stat::make('Выручка сегодня', '₽ ' . number_format($todayRevenue, 0, '.', ' '))
                ->description('За месяц: ₽ ' . number_format($monthRevenue, 0, '.', ' '))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
