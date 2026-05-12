<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('last_name')
                    ->label('Фамилия')
                    ->required()
                    ->maxLength(255),
                TextInput::make('first_name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('middle_name')
                    ->label('Отчество')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required()
                    ->unique('clients', 'phone', ignorable: fn ($record) => $record)
                    ->mask('+7 (999) 999-99-99')
                    ->placeholder('+7 (123) 456-78-90'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),
            ]);
    }
}
