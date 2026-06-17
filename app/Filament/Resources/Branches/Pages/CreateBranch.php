<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Concerns\AddButtonLabels;
use App\Filament\Resources\Branches\BranchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    use AddButtonLabels;

    protected static string $resource = BranchResource::class;

    public function getTitle(): string
    {
        return 'Добавить филиал';
    }
}
