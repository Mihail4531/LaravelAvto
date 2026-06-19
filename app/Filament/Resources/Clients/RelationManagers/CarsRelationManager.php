<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarModel;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarsRelationManager extends RelationManager
{
    protected static string $relationship = 'cars';

    protected static ?string $title = 'Автомобили';

    protected static ?string $modelLabel = 'автомобиль';

    protected static ?string $pluralModelLabel = 'Автомобили';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Автомобиль')
                    ->columns(2)
                    ->schema([
                        Select::make('car_brand_id')
                            ->label('Марка')
                            ->options(CarBrand::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('car_model_id', null)),

                        Select::make('car_model_id')
                            ->label('Модель')
                            ->options(fn (callable $get) => $get('car_brand_id')
                                ? CarModel::where('car_brand_id', $get('car_brand_id'))
                                    ->where('active', true)
                                    ->pluck('name', 'id')
                                : []
                            )
                            ->required()
                            ->searchable(),

                        TextInput::make('license_plate')
                            ->label('Гос. номер')
                            ->placeholder('А123ВС 777')
                            ->maxLength(20)
                            ->nullable(),

                        TextInput::make('vin')
                            ->label('VIN (уникальный)')
                            ->placeholder('Напр. JTDBR32E330079877')
                            ->helperText('17 символов: латинские буквы (кроме I, O, Q) и цифры.')
                            ->maxLength(17)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->rule('regex:/^([A-HJ-NPR-Z0-9]{17})?$/i')
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? strtoupper(trim($state)) : null)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Автомобиль с таким VIN уже зарегистрирован.',
                                'regex' => 'VIN должен состоять ровно из 17 символов — латинских букв (кроме I, O, Q) и цифр.',
                            ])
                            ->nullable(),

                        TextInput::make('body_number')
                            ->label('Номер кузова')
                            ->helperText('У большинства машин совпадает с VIN; заполняется, если отличается.')
                            ->maxLength(50)
                            ->nullable(),

                        TextInput::make('engine_number')
                            ->label('Номер двигателя')
                            ->maxLength(50)
                            ->nullable(),

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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand.name')
                    ->label('Марка')
                    ->sortable(),

                TextColumn::make('model.name')
                    ->label('Модель')
                    ->sortable(),

                TextColumn::make('vin')
                    ->label('VIN')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('year')
                    ->label('Год'),

                TextColumn::make('mileage')
                    ->label('Пробег')
                    ->numeric()
                    ->suffix(' км'),

                TextColumn::make('color')
                    ->label('Цвет'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Изменить'),
            ])
            ->headerActions([
                CreateAction::make()->label('Добавить авто'),
            ]);
    }
}
