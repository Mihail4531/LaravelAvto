<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Part;
use App\Models\PartMovement;
use App\Models\PartRequest;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
        // Форма правки уже добавленной (= выданной) позиции: количество и цена.
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
                    ->fillForm(fn (Model $record) => [
                        'quantity' => $record->pivot->quantity,
                        'price' => $record->pivot->price,
                        'sum' => $record->pivot->sum,
                    ])
                    ->form(fn (Model $record) => [
                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('sum', round((float) $state * (float) ($get('price') ?? 0), 2));
                            })
                            // Потолок = уже списанное на эту позицию + что ещё есть на
                            // складе. Больше выдать нельзя — иначе остаток уйдёт в минус.
                            ->helperText(function () use ($record) {
                                $part = Part::find($record->id);
                                $max = (float) $record->pivot->quantity + (float) ($part?->available_quantity ?? 0);

                                return 'Максимум: '.$max.' '.($part?->unit ?? '').' (на складе ещё доступно '.($part?->available_quantity ?? 0).').';
                            })
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $part = Part::find($record->id);
                                    if (! $part) {
                                        return;
                                    }
                                    $max = (float) $record->pivot->quantity + (float) $part->available_quantity;
                                    if ((float) $value > $max) {
                                        $fail("Недостаточно на складе. Максимум для этой позиции: {$max} {$part->unit} (сейчас доступно {$part->available_quantity} {$part->unit}).");
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
                    ->using(function (Model $record, array $data): Model {
                        // Позиции в наряде всегда выданы (списаны со склада). При
                        // изменении количества корректируем остаток на разницу.
                        $oldQty = (float) $record->pivot->quantity;
                        $newQty = (float) $data['quantity'];
                        $orderId = $this->getOwnerRecord()->id;
                        $part = Part::find($record->id);

                        $delta = $newQty - $oldQty;

                        // Защита от ухода в минус (на случай обхода валидации формы).
                        if ($part && $delta > 0 && $delta > (float) $part->available_quantity) {
                            Notification::make()
                                ->title('Недостаточно на складе')
                                ->body("Доступно: {$part->available_quantity} {$part->unit}. Изменение отменено.")
                                ->danger()
                                ->send();

                            return $record;
                        }
                        if ($part && $delta != 0) {
                            $part->decrement('stock_quantity', $delta);
                            PartMovement::create([
                                'part_id' => $part->id,
                                'order_id' => $orderId,
                                'user_id' => auth()->id(),
                                'type' => $delta > 0 ? PartMovement::TYPE_ISSUE : PartMovement::TYPE_ISSUE_UNDO,
                                'quantity' => abs($delta),
                                'comment' => 'Корректировка количества в заказе №'.$orderId,
                            ]);
                        }

                        $record->pivot->update([
                            'quantity' => $data['quantity'],
                            'price' => $data['price'],
                            'sum' => $data['sum'],
                            'is_issued' => true,
                        ]);

                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();

                        return $record;
                    }),

                DetachAction::make()
                    ->label('Убрать из заказа')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen())
                    ->before(function (Model $record) {
                        $orderId = $this->getOwnerRecord()->id;
                        $qty = (float) $record->pivot->quantity;

                        if ((bool) $record->pivot->is_issued) {
                            // Выданное — возвращаем на склад
                            $record->increment('stock_quantity', $qty);
                            PartMovement::create([
                                'part_id' => $record->id,
                                'order_id' => $orderId,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_ISSUE_UNDO,
                                'quantity' => $qty,
                                'comment' => 'Удаление выданной позиции из заказа №'.$orderId,
                            ]);
                        } else {
                            // Зарезервированное (старые данные) — снимаем резерв
                            $record->decrement('reserved_quantity', $qty);
                            PartMovement::create([
                                'part_id' => $record->id,
                                'order_id' => $orderId,
                                'user_id' => auth()->id(),
                                'type' => PartMovement::TYPE_RELEASE,
                                'quantity' => $qty,
                                'comment' => 'Удаление из заказа №'.$orderId,
                            ]);
                        }
                    })
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->headerActions([
                // Добавление = немедленная выдача: списываем со склада в наряд
                // через PartRequest::fulfill(), поэтому позиция сразу попадает и
                // в наряд, и в журнал «Выдача запчастей».
                Action::make('issuePart')
                    ->label('Добавить запчасть')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen()
                        && auth()->user()?->can('create_part_request'))
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

                        Placeholder::make('compat')
                            ->label('Совместимость')
                            ->content(fn (callable $get) => $this->compatNote($get('part_id'))),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $part = Part::find($get('part_id'));
                                    if ($part && (float) $value > $part->available_quantity) {
                                        $fail("Недостаточно на складе. Доступно: {$part->available_quantity} {$part->unit}.");
                                    }
                                },
                            ]),
                    ])
                    ->action(function (array $data) {
                        try {
                            DB::transaction(function () use ($data) {
                                $request = PartRequest::create([
                                    'order_id' => $this->getOwnerRecord()->id,
                                    'part_id' => $data['part_id'],
                                    'mechanic_id' => auth()->id(),
                                    'quantity' => $data['quantity'],
                                    'status' => PartRequest::STATUS_PENDING,
                                ]);
                                $request->fulfill(auth()->id());
                            });

                            $this->getOwnerRecord()->load('services', 'parts');

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
            ]);
    }
}
