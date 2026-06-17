<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Models\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'История заказ-нарядов';

    protected static ?string $modelLabel = 'заказ-наряд';

    protected static ?string $pluralModelLabel = 'Заказ-наряды';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                TextColumn::make('car.display_name')
                    ->label('Автомобиль'),

                TextColumn::make('branch.name')
                    ->label('Филиал'),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => Order::STATUS_NEW,
                        'warning' => Order::STATUS_IN_PROGRESS,
                        'success' => Order::STATUS_COMPLETED,
                        'primary' => Order::STATUS_CLOSED,
                        'danger' => Order::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn ($state) => Order::statuses()[$state] ?? $state),

                TextColumn::make('total_amount')
                    ->label('Итого')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->headerActions([]);
    }
}
