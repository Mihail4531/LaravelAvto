<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $title = 'Запчасти и материалы';

    protected static ?string $modelLabel = 'запчасть';

    protected static ?string $pluralModelLabel = 'Запчасти и материалы';

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
                        $price = $get('price') ?? 0;
                        $set('sum', round((float) $state * (float) $price, 2));
                    }),

                TextInput::make('price')
                    ->label('Цена за ед. (₽)')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₽')
                    ->required()
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $qty = $get('quantity') ?? 1;
                        $set('sum', round((float) $state * (float) $qty, 2));
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
                    ->label('Ед. изм.'),

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
                    ->using(function (Model $record, array $data): Model {
                        $record->pivot->update([
                            'quantity'  => $data['quantity'],
                            'price'     => $data['price'],
                            'sum'       => $data['sum'],
                            'is_issued' => $data['is_issued'] ?? false,
                        ]);
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                        return $record;
                    }),

                DeleteAction::make()
                    ->label('Удалить')
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Добавить запчасть')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'article'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Запчасть / материал')
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        TextInput::make('price')
                            ->label('Цена за ед. (₽)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₽')
                            ->required(),

                        TextInput::make('sum')
                            ->label('Сумма (₽)')
                            ->numeric()
                            ->prefix('₽')
                            ->default(0),

                        Toggle::make('is_issued')
                            ->label('Выдано клиенту')
                            ->default(false),
                    ])
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ]);
    }
}
