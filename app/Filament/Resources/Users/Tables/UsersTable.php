<?php

namespace App\Filament\Resources\Users\Tables;

use App\Support\AccessLabels;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Фото')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name='
                        .urlencode($record->name).'&background=6366f1&color=fff'),

                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('login')
                    ->label('Логин')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),

                TextColumn::make('position.name')
                    ->label('Должность')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AccessLabels::role($state))
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'director' => 'info',
                        'foreman' => 'success',
                        'mechanic' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('hire_date')
                    ->label('Принят')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('—'),

                IconColumn::make('active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активные')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Неактивные'),

                SelectFilter::make('branch')
                    ->label('Филиал')
                    ->relationship('branch', 'name'),

                TrashedFilter::make()->label('В корзине'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать')
                    // Себя редактируем только через «Мой профиль» — кнопку скрываем.
                    ->visible(fn ($record) => $record->getKey() !== auth()->id()),
                DeleteAction::make()
                    ->label('Удалить')
                    // Нельзя удалить собственную учётную запись.
                    ->visible(fn ($record) => $record->getKey() !== auth()->id()),
                RestoreAction::make()->label('Восстановить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранных')
                        // Свою учётную запись пропускаем даже при массовом удалении.
                        ->action(fn ($records) => $records
                            ->reject(fn ($record) => $record->getKey() === auth()->id())
                            ->each
                            ->delete())
                        ->deselectRecordsAfterCompletion(),
                    RestoreBulkAction::make()->label('Восстановить выбранных'),
                ]),
            ]);
    }
}
