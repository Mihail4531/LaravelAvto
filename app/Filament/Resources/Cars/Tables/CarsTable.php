<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Models\Car;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
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

                TextColumn::make('license_plate')
                    ->label('Гос. номер')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—')
                    ->searchable(),

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

                TextColumn::make('fuel_type')
                    ->label('Топливо')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Car::fuelTypes()[$state] ?? '—')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('engine_volume')
                    ->label('Объём')
                    ->formatStateUsing(fn ($state) => $state ? rtrim(rtrim(number_format((float) $state, 1, '.', ''), '0'), '.').' л' : '—')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('transmission')
                    ->label('КПП')
                    ->formatStateUsing(fn ($state) => Car::transmissions()[$state] ?? '—')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->recordActions([
                EditAction::make()->label('Редактировать')
                    ->visible(fn ($record) => ! $record->trashed() && auth()->user()?->can('update_car')),
                DeleteAction::make()->label('Удалить')
                    ->visible(fn ($record) => ! $record->trashed() && auth()->user()?->can('delete_car')),
                RestoreAction::make()->label('Восстановить')
                    ->visible(fn ($record) => $record->trashed() && auth()->user()?->can('delete_car')),
                ForceDeleteAction::make()->label('Удалить навсегда')
                    ->visible(fn ($record) => $record->trashed() && auth()->user()?->can('delete_car')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                    ForceDeleteBulkAction::make()->label('Удалить навсегда'),
                    RestoreBulkAction::make()->label('Восстановить'),
                ])->visible(fn () => auth()->user()?->can('delete_car')),
            ]);
    }
}
