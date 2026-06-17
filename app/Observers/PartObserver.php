<?php

namespace App\Observers;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PartObserver
{
    /**
     * При изменении остатков (выдача, списание, продажа) проверяем,
     * не опустился ли доступный остаток до минимума. Уведомление шлём
     * только в момент ПЕРЕСЕЧЕНИЯ порога — чтобы не спамить повторно.
     */
    public function updated(Part $part): void
    {
        if (! $part->wasChanged(['stock_quantity', 'reserved_quantity', 'min_stock_quantity'])) {
            return;
        }

        $min = (float) $part->min_stock_quantity;
        if ($min <= 0) {
            return; // контроль минимума выключен для этой позиции
        }

        // Доступный остаток ДО изменения
        $oldStock = (float) $part->getOriginal('stock_quantity');
        $oldReserved = (float) $part->getOriginal('reserved_quantity');
        $oldAvailable = max(0, $oldStock - $oldReserved);

        $newAvailable = (float) $part->available_quantity;

        $wasLow = $oldAvailable <= $min;
        $isLow = $newAvailable <= $min;

        // Уведомляем только при переходе из «нормы» в «дефицит»
        if ($wasLow || ! $isLow) {
            return;
        }

        // Уведомляем только тех, кто может пополнить склад
        // (кладовщик, управляющий, супер-админ), а не всех кто видит запчасти.
        $recipients = User::where('active', true)
            ->get()
            ->filter(fn (User $user) => $user->can('receive_part'));

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('Низкий остаток на складе')
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->body(sprintf(
                '«%s»: доступно %s, минимум %s. Требуется пополнение.',
                $part->name,
                rtrim(rtrim(number_format($newAvailable, 2, '.', ' '), '0'), '.'),
                rtrim(rtrim(number_format($min, 2, '.', ' '), '0'), '.'),
            ))
            ->actions([
                Action::make('open')
                    ->label('Открыть запчасть')
                    ->url(PartResource::getUrl('edit', ['record' => $part->id]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }
}
