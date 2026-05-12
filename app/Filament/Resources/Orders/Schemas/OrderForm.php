<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Car;
use App\Models\User;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Филиал')
                    ->options(Branch::pluck('name', 'id'))
                    ->required(),
                Select::make('client_id')
                    ->label('Клиент')
                    ->options(Client::all()->mapWithKeys(fn($c) => [$c->id => $c->full_name]))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('car_id', null)),
                Select::make('car_id')
                    ->label('Автомобиль')
                    ->options(function (callable $get) {
                        $clientId = $get('client_id');
                        if (!$clientId) return [];
                        return Car::where('client_id', $clientId)->get()->mapWithKeys(fn($car) => [$car->id => $car->display_name]);
                    })
                    ->required(),
                Select::make('receiver_id')
                    ->label('Приёмщик')
                    ->options(User::where('active', true)->pluck('name', 'id'))
                    ->required(),
                DateTimePicker::make('planned_finish')
                    ->label('Плановая дата завершения')
                    ->nullable(),
                DateTimePicker::make('actual_finish')
                    ->label('Фактическая дата завершения')
                    ->nullable(),
                TextInput::make('current_mileage')
                    ->label('Пробег при приёме')
                    ->numeric()
                    ->nullable(),
                Textarea::make('problem_description')
                    ->label('Описание проблемы')
                    ->rows(2)
                    ->nullable(),
                Select::make('status')
                    ->label('Статус')
                    ->options(\App\Models\Order::statuses())
                    ->required(),
                TextInput::make('total_amount')
                    ->label('Итоговая сумма (₽)')
                    ->disabled()
                    ->dehydrated(true) // сохраняется, хотя поле заблокировано
                    ->default(0),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->rows(2)
                    ->nullable(),
            ]);
    }
}
