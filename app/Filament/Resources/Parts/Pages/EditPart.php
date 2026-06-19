<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // Нельзя удалить запчасть с историей (заказы/движения) — блокируем.
                ->before(function (Part $record, DeleteAction $action) {
                    if (! $record->isDeletable()) {
                        Notification::make()
                            ->title('Нельзя удалить запчасть')
                            ->body('«'.$record->name.'» уже использовалась в заказах или есть движения по складу. Снимите флажок «Активна», чтобы убрать её из выбора, — история при этом сохранится.')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
