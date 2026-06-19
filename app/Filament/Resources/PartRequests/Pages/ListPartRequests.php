<?php

namespace App\Filament\Resources\PartRequests\Pages;

use App\Filament\Resources\PartRequests\PartRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartRequests extends ListRecords
{
    protected static string $resource = PartRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Выдать запчасть')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => PartRequestResource::canCreate()),
        ];
    }
}
