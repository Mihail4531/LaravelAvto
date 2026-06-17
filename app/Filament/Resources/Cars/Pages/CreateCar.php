<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCar extends CreateRecord
{
    protected static string $resource = CarResource::class;

    public function getTitle(): string
    {
        return 'Добавить автомобиль';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Добавить');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Добавить и ещё');
    }
}
