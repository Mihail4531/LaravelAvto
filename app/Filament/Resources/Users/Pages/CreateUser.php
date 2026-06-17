<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Добавить сотрудника';
    }
}
