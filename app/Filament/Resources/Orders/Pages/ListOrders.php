<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Order;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать заказ'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => Order::whereNotIn('status', ['closed', 'cancelled', 'completed'])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', ['closed', 'cancelled', 'completed'])),

            'completed' => Tab::make('Завершённые')
                ->badge(fn () => Order::whereIn('status', ['completed', 'closed'])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['completed', 'closed'])),

            'cancelled' => Tab::make('Отменённые')
                ->badge(fn () => Order::where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),

            'deleted' => Tab::make('Удалённые')
                ->badge(fn () => Order::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
