<?php

namespace App\Filament\Resources\Cars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Вместо ID выводим полное имя клиента
                TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(['last_name', 'first_name', 'middle_name'])
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Марка')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model.name')
                    ->label('Модель')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vin')
                    ->label('VIN')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('year')
                    ->label('Год')
                    ->sortable(),

                TextColumn::make('mileage')
                    ->label('Пробег (км)')
                    ->sortable(),

                TextColumn::make('color')
                    ->label('Цвет')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
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
