<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Branch;
use App\Models\Car;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Клиент и автомобиль')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Клиент')
                            ->options(Client::all()->mapWithKeys(fn ($c) => [$c->id => $c->full_name]))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('car_id', null))
                            ->columnSpan(2),

                        Select::make('car_id')
                            ->label('Автомобиль')
                            ->options(function (callable $get) {
                                $clientId = $get('client_id');
                                if (!$clientId) return [];
                                return Car::where('client_id', $clientId)
                                    ->get()
                                    ->mapWithKeys(fn ($car) => [$car->id => $car->display_name]);
                            })
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('current_mileage')
                            ->label('Пробег при приёме (км)')
                            ->numeric()
                            ->nullable()
                            ->suffix('км'),

                        Select::make('branch_id')
                            ->label('Филиал')
                            ->options(Branch::pluck('name', 'id'))
                            ->required(),
                    ]),

                Section::make('Исполнение')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        Select::make('receiver_id')
                            ->label('Приёмщик')
                            ->options(User::where('active', true)->pluck('name', 'id'))
                            ->required(),

                        Select::make('status')
                            ->label('Статус')
                            ->options(Order::statuses())
                            ->required()
                            ->native(false),

                        DateTimePicker::make('planned_finish')
                            ->label('Плановая дата завершения')
                            ->nullable(),

                        DateTimePicker::make('actual_finish')
                            ->label('Фактическая дата завершения')
                            ->nullable(),
                    ]),

                Section::make('Описание и итоги')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Textarea::make('problem_description')
                            ->label('Описание проблемы / жалобы клиента')
                            ->rows(3)
                            ->nullable()
                            ->columnSpan(2),

                        Textarea::make('comment')
                            ->label('Внутренний комментарий')
                            ->rows(2)
                            ->nullable()
                            ->columnSpan(2),

                        TextInput::make('total_amount')
                            ->label('Итоговая сумма (₽)')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0)
                            ->prefix('₽')
                            ->helperText('Пересчитывается автоматически при изменении услуг и запчастей'),
                    ]),
            ]);
    }
}
