<?php

namespace App\Filament\Resources\Clients\Tables;

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

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('last_name')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('middle_name')
                    ->label('Отчество')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
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
