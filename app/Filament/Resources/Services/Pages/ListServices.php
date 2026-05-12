<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Service;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать услугу'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Активные')
                ->badge(fn () => Service::where('active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Неактивные')
                ->badge(fn () => Service::where('active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    }
}
