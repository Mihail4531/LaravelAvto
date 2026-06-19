<?php

namespace App\Filament\Resources\PartRequests\Pages;

use App\Filament\Resources\PartRequests\PartRequestResource;
use App\Models\PartRequest;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePartRequest extends CreateRecord
{
    protected static string $resource = PartRequestResource::class;

    /**
     * Выдачу оформляет текущий сотрудник.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['mechanic_id'] = auth()->id();
        $data['status'] = PartRequest::STATUS_PENDING;

        return $data;
    }

    /**
     * Самовыдача: создаём запись и СРАЗУ списываем со склада в заказ-наряд
     * (PartRequest::fulfill) — без отдельного подтверждения кладовщика. Запись
     * остаётся журналом «кто/когда/что/сколько/какой заказ». Если на складе
     * не хватает или наряд закрыт — откатываем и показываем ошибку на поле.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return DB::transaction(function () use ($data) {
                /** @var PartRequest $record */
                $record = static::getModel()::create($data);
                $record->fulfill(auth()->id());

                return $record;
            });
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages([
                'data.quantity' => $e->getMessage(),
            ]);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Запчасть выдана и списана со склада';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Выдать запчасть');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Выдать и оформить ещё');
    }
}
