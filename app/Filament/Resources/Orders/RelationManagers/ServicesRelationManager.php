<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'Услуги';

    protected static ?string $modelLabel = 'услугу';

    protected static ?string $pluralModelLabel = 'Услуги';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->label('Услуга')
                    ->relationship('services', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('executor_id')
                    ->label('Исполнитель')
                    ->options(User::where('active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

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

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending'     => 'Ожидает',
                        'in_progress' => 'В работе',
                        'done'        => 'Выполнено',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Услуга')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pivot.executor_id')
                    ->label('Исполнитель')
                    ->formatStateUsing(fn ($state) => $state ? (User::find($state)?->name ?? '—') : '—'),

                TextColumn::make('pivot.quantity')
                    ->label('Кол-во')
                    ->numeric(),

                TextColumn::make('pivot.price')
                    ->label('Цена (₽)')
                    ->money('RUB'),

                TextColumn::make('pivot.sum')
                    ->label('Сумма (₽)')
                    ->money('RUB'),

                BadgeColumn::make('pivot.status')
                    ->label('Статус')
                    ->colors([
                        'gray'    => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'done',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'     => 'Ожидает',
                        'in_progress' => 'В работе',
                        'done'        => 'Выполнено',
                        default       => $state,
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Изменить')
                    ->using(function (Model $record, array $data): Model {
                        $record->pivot->update([
                            'executor_id' => $data['executor_id'] ?? null,
                            'quantity'    => $data['quantity'],
                            'price'       => $data['price'],
                            'sum'         => $data['sum'],
                            'status'      => $data['status'],
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
                    ->label('Добавить услугу')
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Услуга')
                            ->required(),

                        Select::make('executor_id')
                            ->label('Исполнитель')
                            ->options(User::where('active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

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

                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending'     => 'Ожидает',
                                'in_progress' => 'В работе',
                                'done'        => 'Выполнено',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->after(function () {
                        $this->getOwnerRecord()->load('services', 'parts');
                        $this->getOwnerRecord()->recalculateTotal();
                    }),
            ]);
    }
}
