<?php

namespace App\Filament\Resources\CarBrands\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\CarBrands\CarBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarBrand extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = CarBrandResource::class;

    public function getTitle(): string
    {
        return 'Добавить марку';
    }

    /**
     * После создания возвращаемся на чистую форму создания, а не на
     * редактирование новой записи. Так удобно вбивать марки подряд и
     * не возникает путаницы «правлю одну и ту же запись вместо новой».
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create');
    }
}
