<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Изображение')->circular()->width(40),
                TextColumn::make('name')->label('Название')->searchable()->sortable(),
                TextColumn::make('parent.name')->label('Родитель')->sortable(),
                TextColumn::make('sort_order')->label('Порядок')->sortable(),
                ToggleColumn::make('active')->label('Активна')->sortable(),
                TextColumn::make('created_at')->label('Создана')->dateTime('d.m.Y H:i')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Обновлена')->dateTime('d.m.Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Редактировать'),
                DeleteAction::make()
                    ->label('Удалить')
                    ->successNotification(null)   // отключаем стандартное уведомление
                    ->action(function ($record) {
                        try {
                            $record->delete();
                            Notification::make()
                                ->success()
                                ->title('Категория удалена')
                                ->send();
                        } catch (QueryException $e) {
                            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')) {
                                Notification::make()
                                    ->danger()
                                    ->title('Невозможно удалить категорию')
                                    ->body('Категория содержит подкатегории или услуги. Сначала удалите или переназначьте их.')
                                    ->send();
                            } else {
                                throw $e;
                            }
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->successNotification(null) // отключаем стандартное уведомление
                        ->action(function ($records) {
                            $errors = [];
                            $deleted = [];
                            foreach ($records as $record) {
                                try {
                                    $record->delete();
                                    $deleted[] = $record->name;
                                } catch (QueryException $e) {
                                    if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'FOREIGN KEY constraint failed')) {
                                        $errors[] = $record->name;
                                    } else {
                                        throw $e;
                                    }
                                }
                            }
                            if (count($deleted) > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Удалено категорий: ' . count($deleted))
                                    ->body('Удалены: ' . implode(', ', $deleted))
                                    ->send();
                            }
                            if (count($errors) > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('Некоторые категории не удалены')
                                    ->body('Следующие категории содержат подкатегории или услуги: ' . implode(', ', $errors))
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
