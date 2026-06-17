<?php

namespace App\Observers;

use App\Filament\Resources\PartRequests\PartRequestResource;
use App\Models\PartRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PartRequestObserver
{
    /**
     * Новая заявка механика — уведомляем тех, кто выдаёт запчасти (кладовщик).
     */
    public function created(PartRequest $request): void
    {
        $recipients = User::where('active', true)
            ->get()
            ->filter(fn (User $user) => $user->can('issue_part'));

        if ($recipients->isEmpty()) {
            return;
        }

        $request->loadMissing('part', 'mechanic');

        Notification::make()
            ->title('Запрос запчасти от механика')
            ->icon('heroicon-o-inbox-arrow-down')
            ->iconColor('warning')
            ->body(sprintf(
                '%s × %s для заказа №%s (механик: %s)',
                $request->part?->name ?? '—',
                $request->quantity,
                $request->order_id,
                $request->mechanic?->name ?? '—',
            ))
            ->actions([
                Action::make('open')
                    ->label('Открыть заявки')
                    ->url(PartRequestResource::getUrl())
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }
}
