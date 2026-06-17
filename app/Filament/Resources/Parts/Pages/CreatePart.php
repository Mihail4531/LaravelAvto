<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;

    public function getTitle(): string
    {
        return 'Добавить запчасть';
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
