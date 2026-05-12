<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Branch;
use App\Models\Appointment;

class AppointmentForm
{
   public static function configure(Schema $schema): Schema
{
    $isEdit = $schema->getOperation() === 'edit'; // true при редактировании, false при создании

    return $schema
        ->components([
            TextInput::make('client_name')
                ->label('Имя клиента')
                ->required()
                ->disabled($isEdit), // отключаем только при редактировании
            TextInput::make('client_phone')
                ->label('Телефон')
                ->tel()
                ->required()
                ->disabled($isEdit),
            TextInput::make('client_email')
                ->label('Email')
                ->email()
                ->disabled($isEdit),
            Textarea::make('problem_description')
                ->label('Описание проблемы')
                ->rows(2)
                ->disabled($isEdit),

            Select::make('branch_id')
                ->label('Филиал')
                ->options(Branch::pluck('name', 'id'))
                ->required()
                ->disabled($isEdit),

            Select::make('time_slot_id')
                ->label('Слот времени')
                ->options(\App\Models\TimeSlot::where('available', true)->get()->mapWithKeys(fn($slot) => [$slot->id => $slot->starts_at]))
                ->nullable()
                ->disabled($isEdit),

            Select::make('car_brand_id')
                ->label('Марка автомобиля')
                ->options(\App\Models\CarBrand::pluck('name', 'id'))
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('car_model_id', null))
                ->disabled($isEdit),
            Select::make('car_model_id')
                ->label('Модель автомобиля')
                ->options(fn (callable $get) =>
                    $get('car_brand_id') ? \App\Models\CarModel::where('car_brand_id', $get('car_brand_id'))->pluck('name', 'id') : []
                )
                ->disabled($isEdit),

            Select::make('status')
                ->label('Статус')
                ->options(\App\Models\Appointment::statuses())
                ->required(),
            Textarea::make('reject_reason')
                ->label('Причина отказа')
                ->rows(2)
                ->visible(fn ($get) => $get('status') === 'rejected')
                ->nullable(),
        ]);
}
}
