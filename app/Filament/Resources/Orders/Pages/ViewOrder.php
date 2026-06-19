<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\PartRequests\Schemas\PartRequestForm;
use App\Models\Order;
use App\Models\Part;
use App\Models\PartRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Выдача запчасти прямо из заказа (списывается сразу)
            Action::make('requestPart')
                ->label('Выдать запчасть')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                // Только для активных нарядов: на завершённый/закрытый/отменённый запчасти не выдают
                ->visible(fn () => auth()->user()?->can('create_part_request')
                    && in_array($this->record->status, [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS], true))
                ->modalHeading('Выдача запчасти на заказ')
                ->modalSubmitActionLabel('Выдать')
                ->schema([
                    Select::make('part_id')
                        ->label('Запчасть / материал')
                        ->options(fn () => Part::where('active', true)
                            ->with('carModels.brand')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Part $p) => [
                                $p->id => $p->name.' ['.$p->applicabilityLabel().'] · доступно '.$p->available_quantity.' '.$p->unit,
                            ]))
                        ->searchable()
                        ->required()
                        ->live(),

                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required()
                        ->live(debounce: 400),

                    Placeholder::make('stock_note')
                        ->label('Наличие')
                        ->content(fn (callable $get) => PartRequestForm::stockNote($get('part_id'), $get('quantity'))),

                    Textarea::make('comment')
                        ->label('Комментарий')
                        ->rows(2)
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    try {
                        DB::transaction(function () use ($data) {
                            $record = PartRequest::create([
                                'order_id' => $this->record->id,
                                'part_id' => $data['part_id'],
                                'mechanic_id' => auth()->id(),
                                'quantity' => $data['quantity'],
                                'status' => PartRequest::STATUS_PENDING,
                                'comment' => $data['comment'] ?? null,
                            ]);
                            $record->fulfill(auth()->id());
                        });

                        Notification::make()
                            ->title('Запчасть выдана и добавлена в заказ')
                            ->success()
                            ->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Не удалось выдать')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Печать заказ-наряда — клиент получает его на руки
            Action::make('print')
                ->label('Печать наряда')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('orders.print', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('view_order')),

            // Кнопка «Редактировать» — только у кого есть update_order
            EditAction::make()
                ->visible(fn () => auth()->user()?->can('update_order')),
        ];
    }
}
