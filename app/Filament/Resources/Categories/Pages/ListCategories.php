<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Category;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать категорию'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => Category::where('active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Неактивные')
                ->badge(fn () => Category::where('active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    }
}
