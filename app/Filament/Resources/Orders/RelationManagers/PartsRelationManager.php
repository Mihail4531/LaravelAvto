<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Part;
use App\Models\PartMovement;
use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $title = 'Запчасти и материалы';

    protected static ?string $modelLabel = 'запчасть';

    protected static ?string $pluralModelLabel = 'Запчасти и материалы';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Мягкая проверка применяемости: подсказка при добавлении запчасти в наряд.
     */
    protected function compatNote($partId): string|HtmlString
    {
        if (! $partId) {
            return '';
        }

        $part = Part::with('carModels.brand')->find($partId);
        if (! $part) {
            return '';
        }

        $modelId = $this->getOwnerRecord()->car?->car_model_id;
        $for = 'Применяемость: '.$part->applicabilityLabel();

        // Нет данных о применяемости — нейтральная подсказка
        if (! $part->is_universal && $part->carModels->isEmpty()) {
            return new HtmlString('<span style="color:#8C95A0;font-size:13px;">'.e($for).'</span>');
        }

        if ($part->fitsModel($modelId)) {
            return new HtmlString('<span style="color:#10B981;font-size:13px;font-weight:600;">✓ Подходит для авто в наряде · '.e($for).'</span>');
        }

        return new HtmlString('<span style="color:#F59E0B;font-size:13px;font-weight:600;">⚠ Не для авто в наряде · '.e($for).'</span>');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('sum', round((float) $state * (float) ($get('price') ?? 0), 2));
                    }),

                TextInput::make('price')
                    ->label('Цена за ед. (₽)')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽')
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('sum', round((float) $state * (float) ($get('quantity') ?? 1), 2));
                    }),

                TextInput::make('sum')
                    ->label('Сумма (₽)')
                    ->numeric()
                    ->prefix('₽')
                    ->disabled()
                    ->dehydrated(true)
                    ->default(0),

                Toggle::make('is_issued')
                    ->label('Выдано клиенту')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Ед.'),

                TextColumn::make('pivot.quantity')
                    ->label('Кол-во')
                    ->numeric(),

                TextColumn::make('pivot.price')
                    ->label('Цена (₽)')
                    ->money('RUB'),

                TextColumn::make('pivot.sum')
                    ->label('Сумма (₽)')
                    ->money('RUB'),

                IconColumn::make('pivot.is_issued')
                    ->label('Выдано')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Изменить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->using(function (Model $record, array $data): Model {
                        $oldQty = (float) $record->pivot->quantity;
                        $oldIssued = (bool) $record->pivot->is_issued;
                        $newQty = (float) $data['quantity'];
                        $newIssued = (bool) ($data['is_issued'] ?? false);
                        $orderId = $this->getOwnerRecord()->id;

                        $part = Part::find($record->id);

                        if (! $oldIssued && ! $newIssued) {
                            // Количество изменилось — корректируем резерв
                            $delta = $newQty - $oldQty;
                            if ($delta != 0) {
                                $part->increment('reserved_quantity', $delta);
                                PartMovement::create([
                                    'part_id' => $part->id,
                                    'order_id' => $orderId,
                                    'user_id' => auth()->id(),
                                    'type' => $delta > 0 ? PartMovement::TYPE_RESERVE : PartMovement::TYPE_RELEASE,
                                    'quantity' => abs($delta),
                                    'comment' => 'Изменение количества в заказе №'.$orderId,
                                ]);
                            }
                        } elseif (! $oldIssued && $newIssued) {
                            // Выдаём: снимаем резерв, списываем со склада
                            $part->decrement('reserved_quantity', $oldQty);
                            $part->decrement('stock_quantity', $newQty);
                            PartMovement::create([
                                'part_id' => $part->id,
                                'order_id' => $orderId,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_ISSUE,
                                'quantity' => $newQty,
                                'comment' => 'Выдача клиенту по заказу №'.$orderId,
                            ]);
                        } elseif ($oldIssued && ! $newIssued) {
                            // Отмена выдачи: возвращаем на склад и резервируем
                            $part->increment('stock_quantity', $oldQty);
                            $part->increment('reserved_quantity', $newQty);
                            PartMovement::create([
                                'part_id' => $part->id,
                                'order_id' => $orderId,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_ISSUE_UNDO,
                                'quantity' => $oldQty,
                                'comment' => 'Отмена выдачи по заказу №'.$orderId,
                            ]);
                        } elseif ($oldIssued && $newIssued) {
                            // Уже выдано, изменили количество — корректируем stock
                            $delta = $newQty - $oldQty;
                            if ($delta != 0) {
                                $part->decrement('stock_quantity', $delta);
                                PartMovement::create([
                                    'part_id' => $part->id,
                                    'order_id' => $orderId,
                                    'user_id' => auth()->id(),
                                    'type' => $delta > 0 ? PartMovement::TYPE_ISSUE : PartMovement::TYPE_ISSUE_UNDO,
                                    'quantity' => abs($delta),
                                    'comment' => 'Корректировка выданного по заказу №'.$orderId,
                                ]);
                            }
                        }

                        $record->pivot->update([
                            'quantity' => $data['quantity'],
                            'price' => $data['price'],
                            'sum' => $data['sum'],
                            'is_issued' => $data['is_issued'] ?? false,
                        ]);

                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();

                        return $record;
                    }),

                DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->before(function (Model $record) {
                        // Снимаем резерв только для невыданных позиций
                        if (! (bool) $record->pivot->is_issued) {
                            $qty = (float) $record->pivot->quantity;
                            $record->decrement('reserved_quantity', $qty);
                            PartMovement::create([
                                'part_id' => $record->id,
                                'order_id' => $this->getOwnerRecord()->id,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_RELEASE,
                                'quantity' => $qty,
                                'comment' => 'Удаление из заказа №'.$this->getOwnerRecord()->id,
                            ]);
                        }
                    })
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Добавить запчасть')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'article'])
                    ->recordSelectOptionsQuery(fn ($query) => $query->where('active', true))
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Запчасть / материал')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $part = Part::find($state);
                                    if ($part) {
                                        $set('price', $part->price);
                                        $set('sum', $part->price);
                                    }
                                }
                            }),

                        Placeholder::make('compat')
                            ->label('Совместимость')
                            ->content(fn (callable $get) => $this->compatNote($get('recordId'))),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('sum', round((float) $state * (float) ($get('price') ?? 0), 2));
                            })
                            ->rules([
                                // Проверяем доступный остаток
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $partId = $get('recordId');
                                    if (! $partId) {
                                        return;
                                    }
                                    $part = Part::find($partId);
                                    if (! $part) {
                                        return;
                                    }
                                    if ((float) $value > $part->available_quantity) {
                                        $fail("Недостаточно на складе. Доступно: {$part->available_quantity} {$part->unit}.");
                                    }
                                },
                            ]),

                        TextInput::make('price')
                            ->label('Цена за ед. (₽)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₽')
                            ->required()
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('sum', round((float) $state * (float) ($get('quantity') ?? 1), 2));
                            }),

                        TextInput::make('sum')
                            ->label('Сумма (₽)')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),
                    ])
                    ->after(function (Model $record) {
                        // Получаем свежеприкреплённую строку пивота
                        $pivot = DB::table('order_part')
                            ->where('order_id', $this->getOwnerRecord()->id)
                            ->where('part_id', $record->id)
                            ->orderByDesc('created_at')
                            ->first();

                        if ($pivot) {
                            $qty = (float) $pivot->quantity;
                            $record->increment('reserved_quantity', $qty);
                            PartMovement::create([
                                'part_id' => $record->id,
                                'order_id' => $this->getOwnerRecord()->id,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_RESERVE,
                                'quantity' => $qty,
                                'comment' => 'Резервирование для заказа №'.$this->getOwnerRecord()->id,
                            ]);
                        }

                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ]);
    }
}
