<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->label('Заказ №')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order.client.full_name')
                    ->label('Клиент')
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Сумма (₽)')
                    ->money('RUB')
                    ->sortable(),

                BadgeColumn::make('method')
                    ->label('Способ оплаты')
                    ->colors([
                        'success' => 'cash',
                        'primary' => 'card',
                        'warning' => 'transfer',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash'     => 'Наличные',
                        'card'     => 'Карта',
                        'transfer' => 'Перевод',
                        default    => $state,
                    }),

                TextColumn::make('cashier.name')
                    ->label('Кассир')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Дата оплаты')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('paid_at', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash'     => 'Наличные',
                        'card'     => 'Карта',
                        'transfer' => 'Перевод',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }
}
