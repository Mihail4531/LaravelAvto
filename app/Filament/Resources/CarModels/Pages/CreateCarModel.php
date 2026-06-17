<?php

namespace App\Filament\Resources\CarModels\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarModel extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = CarModelResource::class;

    public function getTitle(): string
    {
        return 'Добавить модель';
    }

    /**
     * После создания возвращаемся на чистую форму создания, а не на
     * редактирование новой записи — удобно вбивать модели подряд.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }
}
