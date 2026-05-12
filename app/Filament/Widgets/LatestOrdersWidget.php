<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Последние заказы';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with(['client', 'car', 'branch'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(['last_name', 'first_name']),

                TextColumn::make('car.display_name')
                    ->label('Автомобиль'),

                TextColumn::make('branch.name')
                    ->label('Филиал'),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray'    => 'new',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'primary' => 'closed',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => Order::statuses()[$state] ?? $state),

                TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB'),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
