<?php

namespace App\Filament\Resources\CarModels\Pages;

use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCarModels extends ListRecords
{
    protected static string $resource = CarModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Добавить модель')
                ->visible(fn () => CarModelResource::canCreate()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => CarModelResource::getEloquentQuery()
                    ->withoutTrashed()
                    ->where('active', true)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', true)
                ),
            'inactive' => Tab::make('Неактивные')
                ->badge(fn () => CarModelResource::getEloquentQuery()
                    ->withoutTrashed()
                    ->where('active', false)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', false)
                ),
            'deleted' => Tab::make('Удалённые')
                ->badge(fn () => CarModelResource::getEloquentQuery()
                    ->onlyTrashed()
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()
                ),
        ];
    }
}
