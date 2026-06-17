<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\GalleryItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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

                Select::make('size')
                    ->label('Размер плитки')
                    ->options([
                        GalleryItem::SIZE_SMALL => 'Обычная (квадратная)',
                        GalleryItem::SIZE_WIDE => 'Широкая (двойной ширины)',
                        GalleryItem::SIZE_TALL => 'Высокая (портретная)',
                    ])
                    ->default(GalleryItem::SIZE_SMALL)
                    ->required()
                    ->helperText('Используется в сетке витрины для асимметрии.'),

                TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0)
                    ->helperText('Чем меньше число, тем выше в галерее.'),

                Toggle::make('active')
                    ->label('Показывать на сайте')
                    ->default(true),
            ]);
    }
}
