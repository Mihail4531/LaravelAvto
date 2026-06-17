<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return 'Добавить категорию';
    }
}
