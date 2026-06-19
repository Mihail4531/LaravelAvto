<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Branch;
use App\Models\Car;
use App\Models\Client;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Support\BranchScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        // Заказ-наряд длинный: вместо вертикальной простыни секций раскладываем
        // его по вкладкам, чтобы всё помещалось на экран. Гард «только пока
        // заказ открыт» вынесен в замыкание и навешивается на нужные вкладки.
        $openGuard = fn (?Order $record) => $record !== null && ! $record->isOpen();

        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Заказ-наряд')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Клиент и авто')
                            ->icon('heroicon-o-user')
                            ->disabled($openGuard)
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
                                        if (! $clientId) {
                                            return [];
                                        }

                                        return Car::where('client_id', $clientId)
                                            ->get()
                                            ->mapWithKeys(fn ($car) => [$car->id => $car->display_name]);
                                    })
                                    ->required()
                                    ->live()
                                    ->columnSpan(2),

                                // Подробная карточка выбранного авто
                                Placeholder::make('car_details')
                                    ->label('Данные автомобиля')
                                    ->columnSpan(2)
                                    ->content(function (callable $get) {
                                        $carId = $get('car_id');
                                        if (! $carId) {
                                            return '— выберите автомобиль —';
                                        }
                                        $car = Car::with(['brand', 'model'])->find($carId);
                                        if (! $car) {
                                            return '—';
                                        }

                                        $rows = array_filter([
                                            'Марка / модель' => trim(($car->brand?->name ?? '').' '.($car->model?->name ?? '')) ?: null,
                                            'Гос. номер' => $car->license_plate,
                                            'VIN' => $car->vin,
                                            'Год выпуска' => $car->year,
                                            'Цвет' => $car->color,
                                            'Пробег в базе' => $car->mileage ? number_format($car->mileage, 0, '', ' ').' км' : null,
                                            'Топливо' => Car::fuelTypes()[$car->fuel_type] ?? null,
                                            'Двигатель' => $car->engine_volume ? rtrim(rtrim(number_format((float) $car->engine_volume, 1, '.', ''), '0'), '.').' л' : null,
                                            'Мощность' => $car->power ? $car->power.' л.с.' : null,
                                            'КПП' => Car::transmissions()[$car->transmission] ?? null,
                                            'Кузов' => Car::bodyTypes()[$car->body_type] ?? null,
                                        ]);

                                        $html = '<div style="display:grid;grid-template-columns:auto 1fr;gap:4px 16px;font-size:13px;">';
                                        foreach ($rows as $label => $value) {
                                            $html .= '<span style="color:#6E7884;">'.e($label).'</span><span style="font-weight:600;">'.e($value).'</span>';
                                        }
                                        $html .= '</div>';

                                        return new HtmlString($html);
                                    }),

                                Select::make('branch_id')
                                    ->label('Филиал')
                                    ->options(Branch::pluck('name', 'id'))
                                    // Сотрудник работает на своей точке: филиал подставляется
                                    // автоматически и не редактируется. Управляющий (видит всю
                                    // сеть) выбирает филиал вручную. См. App\Support\BranchScope.
                                    ->default(fn () => BranchScope::defaultBranchId())
                                    ->disabled(fn () => BranchScope::isRestricted())
                                    ->dehydrated()
                                    ->required()
                                    ->columnSpan(2),
                            ]),

                        Tab::make('Приёмка')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->disabled($openGuard)
                            ->columns(2)
                            ->schema([
                                TextInput::make('current_mileage')
                                    ->label('Пробег при приёме (км)')
                                    ->numeric()
                                    ->nullable()
                                    ->suffix('км'),

                                Select::make('fuel_level')
                                    ->label('Уровень топлива')
                                    ->options([
                                        'empty' => 'Пустой',
                                        'quarter' => '¼ бака',
                                        'half' => '½ бака',
                                        'three_q' => '¾ бака',
                                        'full' => 'Полный',
                                    ])
                                    ->native(false)
                                    ->nullable(),

                                TextInput::make('equipment')
                                    ->label('Комплектность')
                                    ->placeholder('Запаска, домкрат, магнитола, коврики…')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpan(2),

                                Textarea::make('damages_on_acceptance')
                                    ->label('Повреждения при приёмке')
                                    ->placeholder('Опишите видимые сколы, царапины, вмятины с указанием расположения. Если повреждений нет — укажите «без видимых повреждений».')
                                    ->rows(3)
                                    ->nullable()
                                    ->columnSpan(2),
                            ]),

                        // Подбор услуг прямо при оформлении заказа — нужен для приёмки
                        // «с улицы» без записи (у такого клиента нет заявки, из которой
                        // переносятся работы). При редактировании услугами заведует
                        // ServicesRelationManager, поэтому вкладку показываем только при создании.
                        Tab::make('Услуги')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->visibleOn('create')
                            ->schema([
                                Repeater::make('service_lines')
                                    ->hiddenLabel()
                                    ->addActionLabel('Добавить услугу')
                                    ->default([])
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->schema([
                                        Select::make('service_id')
                                            ->label('Услуга')
                                            ->options(Service::where('active', true)->orderBy('name')->pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('price', (float) (Service::find($state)?->price ?? 0)))
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->label('Кол-во')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->required(),

                                        TextInput::make('price')
                                            ->label('Цена за ед. (₽)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('₽')
                                            ->required(),
                                    ]),
                            ]),

                        Tab::make('Исполнение')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->columns(2)
                            ->schema([
                                Select::make('receiver_id')
                                    ->label('Приёмщик')
                                    ->options(function ($record) {
                                        $users = User::where('active', true)
                                            ->permission('create_order')
                                            ->withoutSuperAdmin()
                                            ->with('position')
                                            ->orderBy('name')
                                            ->get();

                                        // Текущий приёмщик заказа (или тот, кто сейчас
                                        // оформляет) всегда должен быть в списке — иначе
                                        // при техническом/уволенном/вне-выборки пользователе
                                        // поле показало бы голый ID вместо имени.
                                        $currentId = $record?->receiver_id ?? Auth::id();
                                        if ($currentId && ! $users->contains('id', $currentId)) {
                                            if ($current = User::with('position')->find($currentId)) {
                                                $users->push($current);
                                            }
                                        }

                                        return $users->mapWithKeys(fn (User $u) => [
                                            $u->id => $u->name.($u->position ? ' — '.$u->position->name : ''),
                                        ]);
                                    })
                                    ->default(Auth::id())
                                    ->searchable()
                                    ->required()
                                    // Приёмщика задаёт тот, кто оформляет заказ (приёмщик/управляющий).
                                    // Старший мастер координирует, но приёмщика не меняет.
                                    ->disabled(fn () => ! Auth::user()?->can('create_order'))
                                    ->dehydrated(true)
                                    ->helperText(fn () => Auth::user()?->can('create_order')
                                        ? 'Сотрудник, принявший автомобиль. По умолчанию — вы.'
                                        : 'Приёмщика указывает приёмщик при оформлении заказа.'),

                                Select::make('status')
                                    ->label('Статус')
                                    ->options(Order::statuses())
                                    ->default(Order::STATUS_NEW)
                                    ->required()
                                    ->native(false)
                                    // Назначать ход и закрывать заказ-наряд вправе только
                                    // старший мастер (и директор) — change_order_status.
                                    ->disabled(fn () => ! Auth::user()?->can('change_order_status'))
                                    ->dehydrated(true)
                                    ->helperText(fn () => Auth::user()?->can('change_order_status')
                                        ? null
                                        : 'Статус заказ-наряда контролирует старший мастер.'),

                                DateTimePicker::make('planned_finish')
                                    ->label('Плановая дата завершения')
                                    ->nullable(),

                                DateTimePicker::make('actual_finish')
                                    ->label('Фактическая дата завершения')
                                    ->nullable(),
                            ]),

                        Tab::make('Описание и итоги')
                            ->icon('heroicon-o-document-text')
                            ->disabled($openGuard)
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
                    ]),
            ]);
    }
}
