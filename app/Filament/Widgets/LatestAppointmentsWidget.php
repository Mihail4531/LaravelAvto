<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Новые заявки';

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::with(['branch', 'carBrand', 'carModel'])
                    ->where('status', 'new')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('client_name')
                    ->label('Клиент')
                    ->searchable(),

                TextColumn::make('client_phone')
                    ->label('Телефон'),

                TextColumn::make('branch.name')
                    ->label('Филиал'),

                TextColumn::make('carBrand.name')
                    ->label('Марка'),

                TextColumn::make('carModel.name')
                    ->label('Модель'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'confirmed' => 'warning',
                        'rejected' => 'danger',
                        'converted' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => Appointment::statuses()[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
