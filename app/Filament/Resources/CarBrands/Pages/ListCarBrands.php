<?php

namespace App\Filament\Resources\CarBrands\Pages;

use App\Filament\Resources\CarBrands\CarBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCarBrands extends ListRecords
{
    protected static string $resource = CarBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
{
    return [
        'active' => Tab::make('Активные')
            ->badge(fn () => CarBrandResource::getEloquentQuery()
                ->withoutTrashed()
                ->where('active', true)
                ->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->withoutTrashed()->where('active', true)),

        'inactive' => Tab::make('Неактивные')
            ->badge(fn () => CarBrandResource::getEloquentQuery()
                ->withoutTrashed()
                ->where('active', false)
                ->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->withoutTrashed()->where('active', false)),

        'deleted' => Tab::make('Удалённые')
            ->badge(fn () => CarBrandResource::getEloquentQuery()
                ->onlyTrashed()
                ->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->onlyTrashed()),
    ];
}
}
