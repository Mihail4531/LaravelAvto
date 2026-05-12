<?php

namespace App\Filament\Resources\Cars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Client;
use App\Models\CarBrand;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Клиент')
                    ->options(Client::all()->pluck('full_name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Выберите клиента'),

                Select::make('car_brand_id')
                    ->label('Марка')
                    ->options(CarBrand::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('car_model_id', null)),

                Select::make('car_model_id')
                    ->label('Модель')
                    ->options(function (callable $get) {
                        $brandId = $get('car_brand_id');
                        if (!$brandId) {
                            return [];
                        }
                        return \App\Models\CarModel::where('car_brand_id', $brandId)
                            ->where('active', true)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('vin')
                    ->label('VIN (уникальный)')
                    ->maxLength(17)
                    ->unique('cars', 'vin', ignorable: fn ($record) => $record)
                    ->validationMessages(['unique' => 'Автомобиль с таким VIN уже зарегистрирован.']),

                TextInput::make('year')
                    ->label('Год выпуска')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue(now()->year + 1)
                    ->nullable(),

                TextInput::make('mileage')
                    ->label('Пробег (км)')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('color')
                    ->label('Цвет')
                    ->maxLength(50)
                    ->nullable(),
            ]);
    }
}
