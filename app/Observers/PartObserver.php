<?php

namespace App\Observers;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class PartObserver
{
    /**
     * При изменении остатков (выдача, списание, продажа) шлём уведомление
     * только в момент ПЕРЕСЕЧЕНИЯ порога — чтобы не спамить повторно:
     *   1) запчасть закончилась (доступно < 1) — приоритетно, шлём всегда;
     *   2) низкий остаток (доступно ≤ минимума) — если контроль минимума включён.
     */
    public function updated(Part $part): void
    {
        if (! $part->wasChanged(['stock_quantity', 'reserved_quantity', 'min_stock_quantity'])) {
            return;
        }

        // Доступный остаток ДО изменения и ПОСЛЕ
        $oldStock = (float) $part->getOriginal('stock_quantity');
        $oldReserved = (float) $part->getOriginal('reserved_quantity');
        $oldAvailable = max(0, $oldStock - $oldReserved);
        $newAvailable = (float) $part->available_quantity;

        $recipients = $this->recipients();
        if ($recipients->isEmpty()) {
            return;
        }

        // 1) Закончилась совсем — пересечение «было ≥ 1, стало < 1». Шлём
        //    независимо от настройки минимума и не дублируем low-stock.
        if ($oldAvailable >= 1 && $newAvailable < 1) {
            Notification::make()
                ->title('Запчасть закончилась')
                ->icon('heroicon-o-x-circle')
                ->iconColor('danger')
                ->body(sprintf('«%s»: на складе не осталось. Требуется срочное пополнение.', $part->name))
                ->actions([$this->openAction($part)])
                ->sendToDatabase($recipients);

            return;
        }

        // 2) Низкий остаток — пересечение порога минимума
        $min = (float) $part->min_stock_quantity;
        if ($min <= 0) {
            return; // контроль минимума выключен для этой позиции
        }

        $wasLow = $oldAvailable <= $min;
        $isLow = $newAvailable <= $min;
        if ($wasLow || ! $isLow) {
            return; // уведомляем только при переходе из «нормы» в «дефицит»
        }

        Notification::make()
            ->title('Низкий остаток на складе')
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning')
            ->body(sprintf(
                '«%s»: доступно %s, минимум %s. Требуется пополнение.',
                $part->name,
                $this->num($newAvailable),
                $this->num($min),
            ))
            ->actions([$this->openAction($part)])
            ->sendToDatabase($recipients);
    }

    /** Кто может пополнять склад (кладовщик, управляющий, супер-админ). */
    private function recipients(): Collection
    {
        return User::where('active', true)
            ->get()
            ->filter(fn (User $user) => $user->can('receive_part'));
    }

    private function openAction(Part $part): Action
    {
        return Action::make('open')
            ->label('Открыть запчасть')
            ->url(PartResource::getUrl('edit', ['record' => $part->id]))
            ->markAsRead();
    }

    private function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ' '), '0'), '.');
    }
}
