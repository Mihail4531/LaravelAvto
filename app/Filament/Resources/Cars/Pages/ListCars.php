<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Добавить автомобиль'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все')
                ->badge(fn () => CarResource::getEloquentQuery()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query),
            'deleted' => Tab::make('Удалённые')
                ->badge(fn () => CarResource::getEloquentQuery()->onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
