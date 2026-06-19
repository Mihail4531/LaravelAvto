<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Support\BranchScope;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->sortable()
                    // Колонка нужна только когда филиалов больше одного и
                    // пользователь видит всю сеть (см. App\Support\BranchScope).
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),

                TextColumn::make('receiver.name')
                    ->label('Приёмщик')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Order::statuses()[$state] ?? $state)
                    ->color(fn ($state) => Order::statusColor($state))
                    ->icon(fn ($state) => Order::statusIcon($state)),

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
            // Просроченные открытые наряды — акцентная полоса слева (CSS .ais-row-overdue)
            ->recordClasses(fn (Order $record): ?string => $record->isOpen()
                && $record->planned_finish
                && $record->planned_finish->isPast()
                    ? 'ais-row-overdue'
                    : null)
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->relationship('branch', 'name')
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('reports.orders', [
                        'from' => now()->startOfMonth()->format('Y-m-d'),
                        'to' => now()->format('Y-m-d'),
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
                EditAction::make()->label('Редактировать')
                    ->visible(fn ($record) => ! $record->trashed() && auth()->user()?->can('update_order')),
                DeleteAction::make()->label('Удалить')
                    ->visible(fn ($record) => ! $record->trashed() && auth()->user()?->can('delete_order')),
                RestoreAction::make()->label('Восстановить')
                    ->visible(fn ($record) => $record->trashed() && auth()->user()?->can('delete_order')),
                ForceDeleteAction::make()->label('Удалить навсегда')
                    ->visible(fn ($record) => $record->trashed() && auth()->user()?->can('delete_order')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                    ForceDeleteBulkAction::make()->label('Удалить навсегда'),
                    RestoreBulkAction::make()->label('Восстановить'),
                ])->visible(fn () => auth()->user()?->can('delete_order')),
            ]);
    }
}
