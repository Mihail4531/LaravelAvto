<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Order;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(['last_name', 'first_name', 'middle_name'])
                    ->sortable(),

                TextColumn::make('car.display_name')
                    ->label('Автомобиль')
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->sortable(),

                TextColumn::make('receiver.name')
                    ->label('Приёмщик')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'primary' => 'closed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => Order::statuses()[$state] ?? $state),

                TextColumn::make('total_amount')
                    ->label('Сумма (₽)')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('planned_finish')
                    ->label('Плановая дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('actual_finish')
                    ->label('Фактическая дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Удалён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make()->label('В корзине'),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать'),
                DeleteAction::make()->label('Удалить'),
                RestoreAction::make()->label('Восстановить'),
                ForceDeleteAction::make()->label('Удалить навсегда'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                    ForceDeleteBulkAction::make()->label('Удалить навсегда'),
                    RestoreBulkAction::make()->label('Восстановить'),
                ]),
            ]);
    }
}
