<?php

namespace App\Filament\Resources\CarBrands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CarBrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название марки')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 100)   // важно: debounce 100 мс
                    ->afterStateUpdated(fn ($state, callable $set) => $state && $set('slug', Str::slug($state)))
                    ->placeholder('Например: Toyota'),

                TextInput::make('slug')
                    ->label('Символьный код')
                    ->required()
                    ->maxLength(255)
                    ->rule('alpha_dash')
                    ->unique('car_brands', 'slug', ignorable: fn ($record) => $record)
                    ->validationMessages([
                        'unique' => 'Такой символьный код уже используется. Пожалуйста, выберите другой.',
                    ])
                    ->helperText('Только латиница, цифры, дефис. Генерируется автоматически, но можно изменить.'),

                FileUpload::make('logo')
                    ->label('Логотип')
                    ->image()
                    ->disk('public')
                    ->directory('brands/logos')
                    ->visibility('public')
                    ->nullable()
                    ->automaticallyResizeImagesMode('cover')
                    ->automaticallyResizeImagesToWidth(200)
                    ->automaticallyResizeImagesToHeight(200),

                Toggle::make('active')
                    ->label('Активна')
                    ->default(true),
            ]);
    }
}
