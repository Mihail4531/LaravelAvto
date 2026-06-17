<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Models\GalleryItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Фото')
                    ->disk('public')
                    ->square()
                    ->width(60)
                    ->height(60),

                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->placeholder('— без названия —')
                    ->limit(40),

                TextColumn::make('size')
                    ->label('Размер')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        GalleryItem::SIZE_WIDE => 'Широкая',
                        GalleryItem::SIZE_TALL => 'Высокая',
                        default => 'Обычная',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        GalleryItem::SIZE_WIDE => 'info',
                        GalleryItem::SIZE_TALL => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Активна')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Редактировать'),
                DeleteAction::make()->label('Удалить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ]);
    }
}
