<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->label('Фото')
                    ->image()
                    ->disk('public')
                    ->directory('gallery')
                    ->visibility('public')
                    ->required(),

                TextInput::make('title')
                    ->label('Название')
                    ->maxLength(255)
                    ->placeholder('Например: Цех малярных работ')
                    ->nullable(),

                Textarea::make('caption')
                    ->label('Подпись')
                    ->rows(2)
                    ->maxLength(500)
                    ->helperText('Появляется под фото в лайтбоксе.')
                    ->nullable(),

                Toggle::make('active')
                    ->label('Показывать на сайте')
                    ->default(true),
            ]);
    }
}
