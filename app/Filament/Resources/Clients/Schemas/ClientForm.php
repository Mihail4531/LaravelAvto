<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Support\Phone;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ФИО')
                    ->icon('heroicon-o-user')
                    ->description('Фамилия, имя и отчество клиента.')
                    ->columns(3)
                    ->schema([
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
                    ]),

                Section::make('Контакты')
                    ->icon('heroicon-o-phone')
                    ->description('Телефон для связи и email для входа в личный кабинет на сайте.')
                    ->columns(2)
                    ->schema([
                        Phone::configure(TextInput::make('phone'))
                            ->label('Телефон')
                            ->required()
                            ->unique('clients', 'phone', ignorable: fn ($record) => $record)
                            ->validationMessages([
                                'required' => 'Укажите телефон клиента.',
                                'unique' => 'Клиент с таким телефоном уже есть.',
                            ]),
                        TextInput::make('email')
                            ->label('Email')
                            ->helperText('Используется клиентом для входа в личный кабинет на сайте.')
                            ->email()
                            ->required()
                            ->unique('clients', 'email', ignorable: fn ($record) => $record)
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Укажите email клиента.',
                                'email' => 'Введите корректный email.',
                                'unique' => 'Клиент с таким email уже есть.',
                            ]),
                    ]),
            ]);
    }
}
