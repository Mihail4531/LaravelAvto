<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Branch;
use App\Models\Position;
use App\Support\AccessLabels;
use App\Support\Phone;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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

                        TextInput::make('login')
                            ->label('Логин')
                            ->helperText('Имя для входа в АИС.')
                            ->required()
                            ->alphaDash()
                            ->unique('users', 'login', ignorable: fn ($record) => $record)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: fn ($record) => $record)
                            ->maxLength(255),

                        Phone::configure(TextInput::make('phone'))
                            ->label('Телефон')
                            ->nullable()
                            ->maxLength(20),

                        TextInput::make('password')
                            ->label($isEdit ? 'Новый пароль (оставьте пустым чтобы не менять)' : 'Пароль')
                            ->password()
                            ->revealable()
                            ->required(! $isEdit)
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->columnSpan(2),
                    ]),

                Section::make('Должность и филиал')
                    ->columns(2)
                    ->schema([
                        Select::make('position_id')
                            ->label('Должность')
                            ->options(Position::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->placeholder('Выберите должность')
                            ->helperText('Роль доступа в систему определяется автоматически по должности.'),

                        Select::make('branch_id')
                            ->label('Филиал')
                            ->options(Branch::where('active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('Выберите филиал')
                            // От филиала зависит рабочая зона сотрудника: он видит
                            // заказы, заявки и слоты только своей точки. Управляющий
                            // видит всю сеть независимо от филиала.
                            ->helperText('Определяет, данные какого филиала видит сотрудник.'),

                        DatePicker::make('hire_date')
                            ->label('Дата приёма на работу')
                            ->nullable(),

                        Toggle::make('active')
                            ->label('Активен')
                            ->default(true),
                    ]),

                Section::make('Доступ в АИС')
                    ->columns(1)
                    ->schema([
                        Placeholder::make('role_from_position')
                            ->label('Роль от должности')
                            ->content(function (Get $get): string {
                                $positionId = $get('position_id');
                                if (! $positionId) {
                                    return '— должность не выбрана';
                                }

                                $position = Position::find($positionId);
                                if (! $position) {
                                    return '— должность не найдена';
                                }

                                if (! $position->default_role) {
                                    return 'Без доступа в АИС (должность не системная)';
                                }

                                return AccessLabels::role($position->default_role);
                            }),

                        Select::make('roles')
                            ->label('Дополнительные технические роли')
                            ->helperText('Назначается вручную поверх роли от должности. Используется для super_admin (ИТ-сопровождение).')
                            // Раздавать технические роли (super_admin) может только сам
                            // super_admin — иначе управляющий мог бы выдать себе аварийный
                            // доступ и обойти защиту break-glass.
                            ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                            ->multiple()
                            ->preload()
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereNotIn(
                                    'name',
                                    Position::whereNotNull('default_role')->pluck('default_role')->unique()->all()
                                ),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => AccessLabels::role($record->name)),
                    ]),
            ]);
    }
}
