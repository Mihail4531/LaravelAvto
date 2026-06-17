<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return 'Добавить услугу';
    }
}
