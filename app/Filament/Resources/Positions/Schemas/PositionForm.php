<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название должности')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Например: Приёмщик, Мастер, Бухгалтер'),
                TextInput::make('hourly_rate')
                    ->label('Ставка за час (₽)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('₽'),
            ]);
    }
}
