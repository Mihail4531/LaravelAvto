<?php

namespace App\Filament\Resources\CarModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\CarBrand;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class CarModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('car_brand_id')
                    ->label('Марка автомобиля')
                    ->options(CarBrand::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Выберите марку')
                    ->helperText('Сначала создайте марки в разделе "Марки авто".'),

                TextInput::make('name')
                    ->label('Название модели')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 100)
                    ->afterStateUpdated(fn ($state, callable $set) => $state && $set('slug', Str::slug($state)))
                    ->placeholder('Например: Camry'),


                TextInput::make('slug')
                    ->label('Символьный код')
                    ->required()
                    ->rule('alpha_dash')
                    ->unique('car_models', 'slug', ignorable: fn ($record) => $record)
                    ->maxLength(255)
                    ->validationMessages(['unique' => 'Такой символьный код уже используется.'])
                    ->helperText('Только латиница, цифры, дефис. Генерируется автоматически, но можно изменить.'),


           


                Toggle::make('active')
                    ->label('Модель активна')
                    ->default(true),
            ]);
    }
}
