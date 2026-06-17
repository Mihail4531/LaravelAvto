<?php

namespace App\Http\Controllers;

use App\Filament\Support\ExcelExporter;
use App\Models\Appointment;
use App\Models\Car;
use App\Models\Client;
use App\Models\Order;
use App\Models\Part;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    private const PAYMENT_METHODS = [
        'cash' => 'Наличные',
        'card' => 'Карта',
        'transfer' => 'Перевод',
    ];

    private function range(Request $request): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    private function periodLabel(Carbon $from, Carbon $to): string
    {
        return $from->format('d.m.Y').' — '.$to->format('d.m.Y');
    }

    public function orders(Request $request)
    {
        [$from, $to] = $this->range($request);

        $orders = Order::with(['client', 'car.brand', 'car.model', 'branch', 'receiver', 'services', 'parts', 'payments'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id')
            ->get();

        $rows = $orders->map(fn (Order $o) => [
            $o->id,
            $o->created_at->format('d.m.Y H:i'),
            $o->client?->full_name ?: '—',
            $o->client?->phone ?: '—',
            $o->car
                ? trim(($o->car->brand?->name ?? '').' '.($o->car->model?->name ?? ''))
                : '—',
            $o->car?->license_plate ?: '—',
            $o->car?->vin ?: '—',
            $o->branch?->name ?: '—',
            $o->receiver?->name ?: '—',
            Order::statuses()[$o->status] ?? $o->status,
            $o->services->count(),
            $o->parts->count(),
            (float) $o->total_amount,
            (float) $o->paid_amount,
            $o->planned_finish?->format('d.m.Y H:i') ?? '—',
            $o->actual_finish?->format('d.m.Y H:i') ?? '—',
            $o->comment ?? '',
        ]);

        $footer = [[
            'ИТОГО', '', '', '', '', '', '', '', '', '', '', '',
            (float) $orders->sum('total_amount'),
            (float) $orders->sum(fn (Order $o) => $o->paid_amount),
            '', '', '',
        ]];

        return ExcelExporter::stream(
            "zakazy_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}",
            [
                ['label' => '№', 'format' => 'int', 'width' => 7],
                ['label' => 'Дата приёма', 'width' => 16],
                ['label' => 'Клиент'],
                ['label' => 'Телефон', 'width' => 16],
                ['label' => 'Автомобиль'],
                ['label' => 'Гос. номер', 'width' => 13],
                ['label' => 'VIN', 'width' => 19],
                ['label' => 'Филиал'],
                ['label' => 'Приёмщик'],
                ['label' => 'Статус', 'width' => 16],
                ['label' => 'Услуг', 'format' => 'int', 'width' => 8],
                ['label' => 'З/ч', 'format' => 'int', 'width' => 8],
                ['label' => 'Сумма', 'format' => 'money', 'width' => 15],
                ['label' => 'Оплачено', 'format' => 'money', 'width' => 15],
                ['label' => 'План завершения', 'width' => 16],
                ['label' => 'Факт завершения', 'width' => 16],
                ['label' => 'Комментарий', 'width' => 40],
            ],
            $rows,
            'Заказы',
            $footer,
            'Заказы за период: '.$this->periodLabel($from, $to),
        );
    }

    public function payments(Request $request)
    {
        [$from, $to] = $this->range($request);

        $payments = Payment::with(['order.client', 'cashier'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $rows = $payments->map(fn (Payment $p) => [
            $p->id,
            ($p->paid_at ?? $p->created_at)->format('d.m.Y H:i'),
            $p->order_id ? '#'.$p->order_id : '—',
            $p->order?->client?->full_name ?: '—',
            (float) $p->amount,
            self::PAYMENT_METHODS[$p->method] ?? ($p->method ?: '—'),
            $p->cashier?->name ?: '—',
            $p->comment ?? '',
        ]);

        $footer = [[
            'ИТОГО', '', '', '', (float) $payments->sum('amount'), '', '', '',
        ]];

        return ExcelExporter::stream(
            "platezhi_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}",
            [
                ['label' => '№', 'format' => 'int', 'width' => 7],
                ['label' => 'Дата', 'width' => 16],
                ['label' => 'Заказ', 'width' => 10],
                ['label' => 'Клиент'],
                ['label' => 'Сумма', 'format' => 'money', 'width' => 15],
                ['label' => 'Способ оплаты', 'width' => 14],
                ['label' => 'Принял'],
                ['label' => 'Комментарий', 'width' => 40],
            ],
            $rows,
            'Платежи',
            $footer,
            'Платежи за период: '.$this->periodLabel($from, $to),
        );
    }

    public function appointments(Request $request)
    {
        [$from, $to] = $this->range($request);

        $appointments = Appointment::with(['branch', 'timeSlot', 'carBrand', 'carModel', 'services', 'order', 'processedBy'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id')
            ->get();

        $rows = $appointments->map(fn (Appointment $a) => [
            $a->id,
            $a->created_at->format('d.m.Y H:i'),
            $a->client_name,
            $a->client_phone,
            $a->client_email ?: '—',
            trim(($a->carBrand?->name ?? '').' '.($a->carModel?->name ?? '')) ?: '—',
            $a->branch?->name ?: '—',
            $a->timeSlot?->starts_at?->format('d.m.Y H:i') ?? '—',
            $a->services->pluck('name')->implode('; ') ?: '—',
            Appointment::statuses()[$a->status] ?? $a->status,
            $a->order_id ? '#'.$a->order_id : '—',
            $a->processedBy?->name ?: '—',
            $a->processed_at?->format('d.m.Y H:i') ?? '—',
            $a->reject_reason ?? '',
            $a->problem_description ?? '',
        ]);

        return ExcelExporter::stream(
            "zayavki_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}",
            [
                ['label' => '№', 'format' => 'int', 'width' => 7],
                ['label' => 'Дата создания', 'width' => 16],
                ['label' => 'Имя'],
                ['label' => 'Телефон', 'width' => 16],
                ['label' => 'Email', 'width' => 24],
                ['label' => 'Авто'],
                ['label' => 'Филиал'],
                ['label' => 'Слот', 'width' => 16],
                ['label' => 'Услуги', 'width' => 40],
                ['label' => 'Статус', 'width' => 16],
                ['label' => 'Заказ', 'width' => 10],
                ['label' => 'Обработал'],
                ['label' => 'Дата обработки', 'width' => 16],
                ['label' => 'Причина отказа', 'width' => 30],
                ['label' => 'Описание проблемы', 'width' => 40],
            ],
            $rows,
            'Заявки',
            [],
            'Онлайн-заявки за период: '.$this->periodLabel($from, $to),
        );
    }

    public function parts()
    {
        $parts = Part::orderBy('name')->get();

        $rows = $parts->map(function (Part $p) {
            $available = max(0, (float) $p->stock_quantity - (float) $p->reserved_quantity);
            $isLow = $p->min_stock_quantity > 0 && $available <= $p->min_stock_quantity;

            return [
                $p->id,
                $p->article ?: '—',
                $p->name,
                $p->unit ?: 'шт',
                (float) $p->price,
                (float) $p->stock_quantity,
                (float) $p->reserved_quantity,
                $available,
                (float) $p->min_stock_quantity,
                $isLow ? 'ДА' : '—',
                $p->active ? 'Да' : 'Нет',
            ];
        });

        return ExcelExporter::stream(
            'sklad_'.now()->format('Y-m-d'),
            [
                ['label' => '№', 'format' => 'int', 'width' => 7],
                ['label' => 'Артикул', 'width' => 16],
                ['label' => 'Название', 'width' => 40],
                ['label' => 'Ед.', 'width' => 8],
                ['label' => 'Цена', 'format' => 'money', 'width' => 15],
                ['label' => 'На складе', 'format' => 'number', 'width' => 12],
                ['label' => 'Зарезервировано', 'format' => 'number', 'width' => 16],
                ['label' => 'Доступно', 'format' => 'number', 'width' => 12],
                ['label' => 'Минимум', 'format' => 'number', 'width' => 11],
                ['label' => 'Дефицит', 'width' => 10, 'align' => 'center'],
                ['label' => 'Активна', 'width' => 10, 'align' => 'center'],
            ],
            $rows,
            'Склад',
            [],
            'Остатки на складе на '.now()->format('d.m.Y'),
        );
    }

    public function income(Request $request)
    {
        [$from, $to] = $this->range($request);

        $payments = Payment::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($p) => $p->created_at->format('Y-m-d'));

        $rows = collect();
        $totalSum = 0;
        $totalCount = 0;

        foreach ($payments as $date => $dayPayments) {
            $sum = (float) $dayPayments->sum('amount');
            $cnt = $dayPayments->count();
            $totalSum += $sum;
            $totalCount += $cnt;
            $rows->push([
                Carbon::parse($date)->format('d.m.Y'),
                Carbon::parse($date)->translatedFormat('l'),
                $cnt,
                $sum,
                $cnt ? $sum / $cnt : 0,
            ]);
        }

        $footer = [[
            'ИТОГО', '', $totalCount, $totalSum, $totalCount ? $totalSum / $totalCount : 0,
        ]];

        return ExcelExporter::stream(
            "dohody_{$from->format('Y-m-d')}_{$to->format('Y-m-d')}",
            [
                ['label' => 'Дата', 'width' => 14],
                ['label' => 'День недели', 'width' => 16],
                ['label' => 'Платежей', 'format' => 'int', 'width' => 11],
                ['label' => 'Выручка', 'format' => 'money', 'width' => 16],
                ['label' => 'Средний чек', 'format' => 'money', 'width' => 16],
            ],
            $rows,
            'Доходы по дням',
            $footer,
            'Доходы по дням за период: '.$this->periodLabel($from, $to),
        );
    }

    /**
     * Полная база клиентов и их автомобилей: одна строка на автомобиль,
     * данные клиента повторяются. Клиенты без машин тоже попадают в выгрузку.
     */
    public function clients()
    {
        $fuelTypes = Car::fuelTypes();
        $transmissions = Car::transmissions();
        $bodyTypes = Car::bodyTypes();

        $clients = Client::with(['cars.brand', 'cars.model'])
            ->withCount('orders')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = collect();

        foreach ($clients as $client) {
            $base = [
                $client->id,
                $client->last_name ?: '—',
                $client->first_name ?: '—',
                $client->middle_name ?: '—',
                $client->phone ?: '—',
                $client->email ?: '—',
                $client->created_at?->format('d.m.Y') ?? '—',
                $client->orders_count,
            ];

            if ($client->cars->isEmpty()) {
                $rows->push([...$base, '—', '—', '—', '—', '', '', '—', '—', '', '', '—', '—']);

                continue;
            }

            foreach ($client->cars as $car) {
                $rows->push([
                    ...$base,
                    $car->brand?->name ?: '—',
                    $car->model?->name ?: '—',
                    $car->license_plate ?: '—',
                    $car->vin ?: '—',
                    $car->year ?: '',
                    $car->mileage ?: '',
                    $car->color ?: '—',
                    $fuelTypes[$car->fuel_type] ?? ($car->fuel_type ?: '—'),
                    $car->engine_volume ? (float) $car->engine_volume : '',
                    $car->power ?: '',
                    $transmissions[$car->transmission] ?? ($car->transmission ?: '—'),
                    $bodyTypes[$car->body_type] ?? ($car->body_type ?: '—'),
                ]);
            }
        }

        return ExcelExporter::stream(
            'klienty_i_avtomobili_'.now()->format('Y-m-d'),
            [
                ['label' => 'ID', 'format' => 'int', 'width' => 7],
                ['label' => 'Фамилия'],
                ['label' => 'Имя'],
                ['label' => 'Отчество'],
                ['label' => 'Телефон', 'width' => 16],
                ['label' => 'Email', 'width' => 26],
                ['label' => 'Регистрация', 'width' => 13],
                ['label' => 'Заказов', 'format' => 'int', 'width' => 10],
                ['label' => 'Марка', 'width' => 16],
                ['label' => 'Модель', 'width' => 16],
                ['label' => 'Гос. номер', 'width' => 13],
                ['label' => 'VIN', 'width' => 19],
                ['label' => 'Год', 'format' => 'int', 'width' => 8],
                ['label' => 'Пробег, км', 'format' => 'int', 'width' => 12],
                ['label' => 'Цвет', 'width' => 14],
                ['label' => 'Топливо', 'width' => 12],
                ['label' => 'Объём, л', 'format' => 'number', 'width' => 10],
                ['label' => 'Мощность, л.с.', 'format' => 'int', 'width' => 14],
                ['label' => 'КПП', 'width' => 18],
                ['label' => 'Тип кузова', 'width' => 22],
            ],
            $rows,
            'Клиенты и авто',
            [],
            'База клиентов и автомобилей на '.now()->format('d.m.Y'),
        );
    }
}
