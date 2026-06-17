<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Филиал')
                    ->options(Branch::pluck('name', 'id'))
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->label('Начало')
                    ->required()
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i'),
                DateTimePicker::make('ends_at')
                    ->label('Окончание')
                    ->required()
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i')
                    ->afterOrEqual('starts_at'),
                Toggle::make('available')
                    ->label('Доступен для записи')
                    ->default(true),
            ]);
    }
}
