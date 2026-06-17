<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    // По умолчанию заголовок берётся из recordTitleAttribute (id).
    // Показываем понятное «Заказ-наряд №N».
    public function getTitle(): string
    {
        return 'Заказ-наряд №'.$this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Печать заказ-наряда — клиент получает его на руки
            Action::make('print')
                ->label('Печать наряда')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('orders.print', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('view_order')),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
