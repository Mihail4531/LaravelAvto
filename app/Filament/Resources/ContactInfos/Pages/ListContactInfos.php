<?php

namespace App\Filament\Resources\ContactInfos\Pages;

use App\Filament\Resources\ContactInfos\ContactInfoResource;
use Filament\Resources\Pages\ListRecords;

class ListContactInfos extends ListRecords
{
    protected static string $resource = ContactInfoResource::class;

    // Singleton — создавать новые записи нельзя, кнопку «Создать» убираем.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
