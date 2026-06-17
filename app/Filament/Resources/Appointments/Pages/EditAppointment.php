<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Раскладываем сохранённое ФИО (client_name) обратно по трём полям формы,
     * чтобы они отображались при открытии заявки. Сами поля на редактировании
     * заблокированы, поэтому обратно в client_name ничего не пишется.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $parts = preg_split('/\s+/', trim((string) ($data['client_name'] ?? '')), 3);

        $data['client_last_name'] = $parts[0] ?? '';
        $data['client_first_name'] = $parts[1] ?? '';
        $data['client_middle_name'] = $parts[2] ?? '';

        return $data;
    }
}
