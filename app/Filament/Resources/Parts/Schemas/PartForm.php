<?php

namespace App\Filament\Resources\Parts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('article')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('unit')
                    ->required()
                    ->default('шт'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reserved_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('location'),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
