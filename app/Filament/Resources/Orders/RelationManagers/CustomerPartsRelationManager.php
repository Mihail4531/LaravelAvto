<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Давальческие запчасти — те, что клиент привёз сам.
 * Без склада и без стоимости: за саму деталь денег не берём, берём за работу.
 */
class CustomerPartsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerParts';

    protected static ?string $title = 'Запчасти клиента';

    protected static ?string $modelLabel = 'запчасть клиента';

    protected static ?string $pluralModelLabel = 'Запчасти клиента';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Наименование')
                    ->placeholder('Напр.: масляный фильтр Mann, масло 5W-30')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(0.01)
                    ->default(1)
                    ->required(),

                TextInput::make('unit')
                    ->label('Ед. изм.')
                    ->default('шт')
                    ->maxLength(20)
                    ->required(),

                TextInput::make('note')
                    ->label('Примечание')
                    ->placeholder('Состояние, особенности — по желанию')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->numeric(),

                TextColumn::make('unit')
                    ->label('Ед.'),

                TextColumn::make('note')
                    ->label('Примечание')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить запчасть клиента')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Изменить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen()),

                DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn () => $this->getOwnerRecord()->isOpen()),
            ])
            ->emptyStateHeading('Клиент свои запчасти не привозил')
            ->emptyStateDescription('Здесь учитываются детали, которые привёз сам клиент. За них денег не берём — только за работу.');
    }
}
