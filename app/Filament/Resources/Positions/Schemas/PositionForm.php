<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Support\AccessLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

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

                Select::make('default_role')
                    ->label('Роль доступа в АИС')
                    ->helperText('Какие права получит сотрудник на этой должности. Оставьте пустым, если должность не работает в системе (уборщик, разнорабочий).')
                    ->options(fn () => Role::where('guard_name', 'web')
                        ->where('name', '!=', 'super_admin')
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->map(fn (string $name) => AccessLabels::role($name))
                        ->all())
                    ->searchable()
                    ->nullable()
                    ->placeholder('Без доступа в АИС'),
            ]);
    }
}
