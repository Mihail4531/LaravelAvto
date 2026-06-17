<?php

namespace App\Filament\Resources\PartRequests\Pages;

use App\Filament\Resources\PartRequests\PartRequestResource;
use App\Models\PartRequest;
use Filament\Resources\Pages\CreateRecord;

class CreatePartRequest extends CreateRecord
{
    protected static string $resource = PartRequestResource::class;

    /**
     * Заявку создаёт текущий механик; статус — «ожидает выдачи».
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['mechanic_id'] = auth()->id();
        $data['status'] = PartRequest::STATUS_PENDING;

        return $data;
    }
}
