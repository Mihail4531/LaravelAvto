<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Автомобиль')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Клиент')
                            ->options(Client::all()->pluck('full_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Выберите клиента')
                            ->columnSpan(2),

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
                                if (! $brandId) {
                                    return [];
                                }

                                return CarModel::where('car_brand_id', $brandId)
                                    ->where('active', true)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('license_plate')
                            ->label('Гос. номер')
                            ->placeholder('А123ВС 777')
                            ->maxLength(20)
                            ->nullable(),

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

                        TextInput::make('color')
                            ->label('Цвет')
                            ->maxLength(50)
                            ->nullable(),

                        TextInput::make('mileage')
                            ->label('Пробег (км)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ]),

                Section::make('Характеристики')
                    ->description('Параметры конкретного автомобиля (двигатель, КПП, кузов). Необязательно.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        Select::make('fuel_type')
                            ->label('Тип топлива')
                            ->options(Car::fuelTypes())
                            ->native(false)
                            ->placeholder('Не указан'),

                        TextInput::make('engine_volume')
                            ->label('Объём двигателя')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(15)
                            ->step(0.1)
                            ->suffix('л')
                            ->placeholder('Например: 3.0'),

                        TextInput::make('power')
                            ->label('Мощность')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(2000)
                            ->suffix('л.с.')
                            ->placeholder('Например: 249'),

                        Select::make('transmission')
                            ->label('Коробка передач')
                            ->options(Car::transmissions())
                            ->native(false)
                            ->placeholder('Не указана'),

                        Select::make('body_type')
                            ->label('Тип кузова')
                            ->options(Car::bodyTypes())
                            ->native(false)
                            ->placeholder('Не указан')
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
