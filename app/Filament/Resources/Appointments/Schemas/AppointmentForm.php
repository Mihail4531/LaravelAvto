<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\TimeSlot;
use App\Support\BranchScope;
use App\Support\Phone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = $schema->getOperation() === 'edit';

        return $schema
            ->components([
                // ФИО храним одной колонкой client_name, но вводим по частям.
                // Склейка/разбор — на страницах CreateAppointment/EditAppointment.
                TextInput::make('client_last_name')
                    ->label('Фамилия')
                    ->required()
                    ->disabled($isEdit),
                TextInput::make('client_first_name')
                    ->label('Имя')
                    ->required()
                    ->disabled($isEdit),
                TextInput::make('client_middle_name')
                    ->label('Отчество')
                    ->nullable()
                    ->disabled($isEdit),
                Phone::configure(TextInput::make('client_phone'))
                    ->label('Телефон')
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
                    // Включаем soft-deleted, чтобы существующая заявка не падала
                    ->options(fn ($record) => Branch::withTrashed()
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    // Сотрудник видит только свою точку — филиал подставляется и
                    // блокируется. Управляющий выбирает вручную (см. BranchScope).
                    ->default(fn () => BranchScope::defaultBranchId())
                    ->dehydrated()
                    ->required()
                    ->disabled($isEdit || BranchScope::isRestricted()),

                Select::make('time_slot_id')
                    ->label('Слот времени')
                    // Показываем доступные будущие слоты + текущий слот заявки
                    // (он уже зарезервирован, available=false; для редактирования
                    // оставляем его, даже если время уже прошло).
                    ->options(function ($record) {
                        $query = TimeSlot::query()
                            ->where(function ($q) use ($record) {
                                $q->bookable();
                                if ($record?->time_slot_id) {
                                    $q->orWhere('id', $record->time_slot_id);
                                }
                            })
                            ->orderBy('starts_at');

                        return $query->get()->mapWithKeys(
                            fn ($slot) => [$slot->id => $slot->starts_at->format('d.m.Y H:i').($slot->available ? '' : ' (занят)')]
                        );
                    })
                    ->nullable()
                    ->disabled($isEdit),

                Select::make('car_brand_id')
                    ->label('Марка автомобиля')
                    ->options(fn ($record) => CarBrand::withTrashed()->orderBy('name')->pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('car_model_id', null))
                    ->disabled($isEdit),

                Select::make('car_model_id')
                    ->label('Модель автомобиля')
                    ->options(function (callable $get, $record) {
                        $brandId = $get('car_brand_id') ?? $record?->car_brand_id;
                        if (! $brandId) {
                            return [];
                        }

                        return CarModel::withTrashed()
                            ->where('car_brand_id', $brandId)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->disabled($isEdit),

                Select::make('status')
                    ->label('Статус')
                    // «Преобразована в заказ» — системный статус: его выставляет
                    // ТОЛЬКО кнопка «Преобразовать в заказ» (создаёт клиента, авто
                    // и заказ-наряд). Вручную выбрать его нельзя — иначе заявка
                    // выглядела бы преобразованной без реального заказа.
                    ->options(function ($record) {
                        $statuses = Appointment::statuses();

                        if ($record?->status !== Appointment::STATUS_CONVERTED) {
                            unset($statuses[Appointment::STATUS_CONVERTED]);
                        }

                        return $statuses;
                    })
                    // Уже преобразованную заявку «откатить» нельзя — статус заблокирован.
                    ->disabled(fn ($record) => $record?->status === Appointment::STATUS_CONVERTED)
                    ->required(),

                Textarea::make('reject_reason')
                    ->label('Причина отказа')
                    ->rows(2)
                    ->visible(fn ($get) => $get('status') === 'rejected')
                    ->nullable(),
            ]);
    }
}
