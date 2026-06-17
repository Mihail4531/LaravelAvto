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

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Механик запрашивает запчасть прямо из своего заказа
            Action::make('requestPart')
                ->label('Запросить запчасть')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                // Только для активных нарядов: на завершённый/закрытый/отменённый запчасти не запрашивают
                ->visible(fn () => auth()->user()?->can('create_part_request')
                    && in_array($this->record->status, [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS], true))
                ->modalHeading('Запрос запчасти у склада')
                ->modalSubmitActionLabel('Отправить кладовщику')
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
                        ->label('Комментарий для кладовщика')
                        ->rows(2)
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    PartRequest::create([
                        'order_id' => $this->record->id,
                        'part_id' => $data['part_id'],
                        'mechanic_id' => auth()->id(),
                        'quantity' => $data['quantity'],
                        'status' => PartRequest::STATUS_PENDING,
                        'comment' => $data['comment'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Заявка на запчасть отправлена кладовщику')
                        ->success()
                        ->send();
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
