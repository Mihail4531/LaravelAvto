<?php

namespace App\Observers;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AppointmentObserver
{
    /**
     * При создании новой заявки уведомляем сотрудников,
     * которые имеют право работать с заявками.
     */
    public function created(Appointment $appointment): void
    {
        $recipients = User::where('active', true)
            ->get()
            ->filter(fn (User $user) => $user->can('view_any_appointment'));

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Новая заявка на обслуживание')
            ->icon('heroicon-o-calendar-days')
            ->iconColor('info')
            ->body(sprintf(
                'Клиент: %s, тел. %s',
                $appointment->client_name ?: '—',
                $appointment->client_phone ?: '—',
            ))
            ->actions([
                Action::make('open')
                    ->label('Открыть заявку')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment->id]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }
}
