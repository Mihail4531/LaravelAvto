<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Branch;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = $schema->getOperation() === 'edit';

        return $schema
            ->columns(2)
            ->components([
                Section::make('Личные данные')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Полное имя')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: fn ($record) => $record)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->nullable()
                            ->maxLength(20),

                        TextInput::make('password')
                            ->label($isEdit ? 'Новый пароль (оставьте пустым для сохранения)' : 'Пароль')
                            ->password()
                            ->required(!$isEdit)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->minLength(8)
                            ->columnSpan(2),
                    ]),

                Section::make('Должность и филиал')
                    ->columns(2)
                    ->schema([
                        Select::make('position_id')
                            ->label('Должность')
                            ->options(Position::pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Выберите должность'),

                        Select::make('branch_id')
                            ->label('Филиал')
                            ->options(Branch::where('active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Выберите филиал'),

                        DatePicker::make('hire_date')
                            ->label('Дата приёма на работу')
                            ->nullable(),

                        Toggle::make('active')
                            ->label('Активен')
                            ->default(true),
                    ]),

                Section::make('Роли')
                    ->schema([
                        Select::make('roles')
                            ->label('Роли')
                            ->multiple()
                            ->options(Role::pluck('name', 'name'))
                            ->preload()
                            ->relationship('roles', 'name'),
                    ]),
            ]);
    }
}
