<?php

namespace App\Filament\Resources\Parts\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        TextInput::make('article')
                            ->label('Артикул')
                            ->required()
                            ->maxLength(100)
                            ->unique('parts', 'article', ignorable: fn ($record) => $record)
                            ->placeholder('Например: OIL-5W30-4L'),

                        TextInput::make('name')
                            ->label('Наименование')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->placeholder('Например: Масло моторное 5W-30 4л'),

                        TextInput::make('unit')
                            ->label('Единица измерения')
                            ->required()
                            ->default('шт')
                            ->maxLength(20),

                        TextInput::make('price')
                            ->label('Цена (₽)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₽'),

                        TextInput::make('location')
                            ->label('Место хранения')
                            ->maxLength(100)
                            ->placeholder('Например: Стеллаж А3'),
                    ]),

                Section::make('Остатки')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label('На складе')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('reserved_quantity')
                            ->label('Зарезервировано')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('active')
                            ->label('Активна')
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
