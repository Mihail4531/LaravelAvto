<?php

namespace App\Filament\Resources\Parts\Tables;

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
                    ->color(fn ($record) => $record->available_quantity <= 0 ? 'danger' : 'success'),

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
