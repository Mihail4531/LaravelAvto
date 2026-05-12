<?php

namespace App\Filament\Resources\CarModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CarModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand.name')
                    ->label('Марка')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название модели')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Символьный код')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('active')
                    ->label('Активна')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Удалена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('brand.name')
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
