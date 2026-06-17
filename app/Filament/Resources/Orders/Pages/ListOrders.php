<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return OrderResource::isLimitedToOwn() ? 'Мои наряды' : 'Заказ-наряды';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать заказ-наряд')
                ->visible(fn () => OrderResource::canCreate()),
        ];
    }

    /**
     * Базовый запрос для счётчиков вкладок — с тем же scope, что и список
     * (механик считает только свои наряды).
     */
    private function baseCountQuery(): Builder
    {
        return Order::query()->when(
            OrderResource::isLimitedToOwn(),
            fn (Builder $q) => $q->whereHas('services', fn (Builder $s) => $s->where('executor_id', auth()->id()))
        );
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => $this->baseCountQuery()->whereNotIn('status', ['closed', 'cancelled', 'completed'])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->whereNotIn('status', ['closed', 'cancelled', 'completed'])),

            'completed' => Tab::make('Завершённые')
                ->badge(fn () => $this->baseCountQuery()->whereIn('status', ['completed', 'closed'])->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->whereIn('status', ['completed', 'closed'])),

            'cancelled' => Tab::make('Отменённые')
                ->badge(fn () => $this->baseCountQuery()->where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'cancelled')),

            'deleted' => Tab::make('Удалённые')
                ->badge(fn () => $this->baseCountQuery()->onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
