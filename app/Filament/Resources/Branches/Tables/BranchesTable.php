<?php

namespace App\Filament\Resources\Branches\Tables;

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

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название')->searchable()->sortable(),
                TextColumn::make('city')->label('Город')->searchable()->sortable(),
                TextColumn::make('address')->label('Адрес')->searchable()->toggleable(),
                TextColumn::make('phone')->label('Телефон')->searchable(),
                TextColumn::make('email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('work_schedule')
                    ->label('Режим работы')
                    ->getStateUsing(fn ($record) => $record->work_schedule ?? '—')
                    ->searchable(false),
                ToggleColumn::make('active')
                    ->label('Активен')
                    ->sortable(),
                TextColumn::make('created_at')->label('Создан')->dateTime('d.m.Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Обновлён')->dateTime('d.m.Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Удалён')->dateTime('d.m.Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
