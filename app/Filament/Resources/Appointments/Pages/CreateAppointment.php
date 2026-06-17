<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    // Заявку сотрудник оформляет (напр. при звонке), поэтому оставляем
    // «Создать»/«Создание» — трейт AddButtonLabels здесь намеренно не нужен.
    public function getTitle(): string
    {
        return 'Создание заявки';
    }

    /**
     * ФИО вводится тремя полями — склеиваем их в одну колонку client_name
     * (trim в модели) и убираем виртуальные ключи перед сохранением.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['client_name'] = Appointment::composeName(
            $data['client_last_name'] ?? null,
            $data['client_first_name'] ?? null,
            $data['client_middle_name'] ?? null,
        );

        unset($data['client_last_name'], $data['client_first_name'], $data['client_middle_name']);

        return $data;
    }
}
