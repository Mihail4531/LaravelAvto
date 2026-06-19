<?php

namespace App\Filament\Resources\ContactInfos\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactInfosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—'),

                TextColumn::make('working_hours')
                    ->label('Часы работы')
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            // Singleton: одна запись, правится в модальном окне.
            ->recordActions([
                EditAction::make()->label('Редактировать'),
            ]);
    }
}
