<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название категории')
                    ->required()
                    ->live(debounce: 100)
                    ->afterStateUpdated(fn ($state, callable $set) => $state && $set('slug', Str::slug($state)))
                    ->placeholder('Например: Диагностика'),
                TextInput::make('slug')
                    ->label('Символьный код')
                    ->required()
                    ->rule('alpha_dash')
                    ->unique('categories', 'slug', ignorable: fn ($record) => $record)
                    ->maxLength(255)
                    ->validationMessages(['unique' => 'Такой символьный код уже используется.'])
                    ->helperText('Только латиница, цифры, дефис. Генерируется автоматически.'),
                FileUpload::make('image')
                    ->label('Изображение категории')
                    ->image()
                    ->disk('public')
                    ->directory('categories')
                    ->visibility('public')
                    ->nullable(),
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->nullable(),
                TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0)
                    ->helperText('Чем меньше число, тем выше категория в списке.'),
                Toggle::make('active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }
}
