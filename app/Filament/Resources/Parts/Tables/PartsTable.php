<?php

namespace App\Filament\Resources\Parts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Ед. изм.')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена (₽)')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('На складе')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reserved_quantity')
                    ->label('Резерв')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('available_quantity')
                    ->label('Свободно')
                    ->numeric()
                    ->sortable(false)
                    ->color(fn ($record) => match (true) {
                        $record->available_quantity <= 0 => 'danger',
                        $record->min_stock_quantity > 0 && $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn ($record) => $record->isLowStock()
                        ? "Мало: минимум {$record->min_stock_quantity} {$record->unit}"
                        : null
                    ),

                TextColumn::make('applicability')
                    ->label('Применяемость')
                    ->state(fn ($record) => $record->applicabilityLabel())
                    ->badge()
                    ->color(fn ($record) => $record->is_universal ? 'info' : ($record->carModels->isNotEmpty() ? 'success' : 'gray'))
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('location')
                    ->label('Место хранения')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('active')
                    ->label('Активна')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Добавлена')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активные')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('reports.parts'))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать')
                    ->visible(fn () => auth()->user()?->can('update_part')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ])->visible(fn () => auth()->user()?->can('delete_part')),
            ]);
    }
}
