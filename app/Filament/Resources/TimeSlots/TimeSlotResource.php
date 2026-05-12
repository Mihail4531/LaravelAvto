<?php

namespace App\Filament\Resources\TimeSlots;

use App\Filament\Resources\TimeSlots\Pages\CreateTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\EditTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\ListTimeSlots;
use App\Filament\Resources\TimeSlots\Schemas\TimeSlotForm;
use App\Filament\Resources\TimeSlots\Tables\TimeSlotsTable;
use App\Models\TimeSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Слоты времени';
    protected static ?string $modelLabel = 'слот';
    protected static ?string $pluralModelLabel = 'Слоты времени';
    protected static string|UnitEnum|null $navigationGroup = 'Управление записью';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TimeSlotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeSlotsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeSlots::route('/'),
            'create' => CreateTimeSlot::route('/create'),
            'edit' => EditTimeSlot::route('/{record}/edit'),
        ];
    }
}
