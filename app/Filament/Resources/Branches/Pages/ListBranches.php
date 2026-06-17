<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Добавить филиал')
                ->visible(fn () => BranchResource::canCreate()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => BranchResource::getEloquentQuery()
                    ->withoutTrashed()
                    ->where('active', true)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', true)),

            'inactive' => Tab::make('Неактивные')
                ->badge(fn () => BranchResource::getEloquentQuery()
                    ->withoutTrashed()
                    ->where('active', false)
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', false)),

            'deleted' => Tab::make('Удалённые')
                ->badge(fn () => BranchResource::getEloquentQuery()
                    ->onlyTrashed()
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
