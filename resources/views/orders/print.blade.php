@php
    /** @var \App\Models\Order $order */
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');

    $car = $order->car;
    $branch = $order->branch;

    $servicesSum = $order->services->sum('pivot.sum');
    $partsSum = $order->parts->sum('pivot.sum');
    $grand = $servicesSum + $partsSum;

    // Исполнители услуг — соберём имена одним запросом (без N+1)
    $executorIds = $order->services->pluck('pivot.executor_id')->filter()->unique();
    $executors = $executorIds->isNotEmpty()
        ? \App\Models\User::whereIn('id', $executorIds)->pluck('name', 'id')
        : collect();

    // Описание дефектов / комментарий
    $defects = $order->problem_description ?: $order->damages_on_acceptance ?: '';

    // Минимальное число строк в таблицах (как в бланке) — добиваем пустыми
    $workPad = max(0, 4 - $order->services->count());
    $matPad = max(0, 2 - $order->parts->count());
    $custPad = max(0, 2 - $order->customerParts->count());
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заказ-наряд №{{ $order->id }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, 'DejaVu Sans', sans-serif;
            color: #000;
            font-size: 11px;
            line-height: 1.25;
            margin: 0;
            background: #f3f4f6;
        }
        .sheet { background: #fff; max-width: 210mm; margin: 10px auto; padding: 8mm; }
        .toolbar { max-width: 210mm; margin: 10px auto 0; text-align: right; }
        .btn { display: inline-block; background: #0066B3; color: #fff; border: 0; padding: 7px 14px; border-radius: 6px; font-size: 12px; cursor: pointer; text-decoration: none; }

        h1 { font-size: 15px; margin: 0 0 4px; font-weight: 700; }
        h2 { font-size: 13px; margin: 10px 0 4px; font-weight: 700; }

        .head { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #000; padding-bottom: 8px; }
        .head .col { width: 48%; }
        .lines div { padding: 0; }
        .lines .v { font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        .vehicle td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; width: 33.33%; }
        .vehicle .lbl { font-size: 10px; }
        .vehicle .val { font-weight: 700; min-height: 14px; margin-top: 1px; }

        .items th, .items td { border: 1px solid #000; padding: 3px 4px; vertical-align: top; }
        .items th { font-weight: 400; text-align: center; font-size: 10px; }
        .items td { height: 22px; }
        .items .c { text-align: center; }
        .items .r { text-align: right; white-space: nowrap; }
        .items .total td { border: 0; padding-top: 4px; }
        .items .total .lbl { text-align: right; }
        .items .total .box { border: 1px solid #000; text-align: right; font-weight: 700; }

        .cust th, .cust td { border: 1px solid #000; padding: 3px 6px; }
        .cust th { font-weight: 400; text-align: left; }

        .foot { margin-top: 12px; border-top: 1px solid #000; padding-top: 8px; font-size: 11px; }
        .sign-row { margin-top: 10px; }
        .ln { display: inline-block; border-bottom: 1px solid #000; min-width: 160px; }
        .muted { color: #555; }

        /* Обёртка для широких таблиц: горизонтальный скролл на узких экранах */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Адаптив ТОЛЬКО для экрана (печать остаётся строго A4) */
        @media screen and (max-width: 760px) {
            body { font-size: 12px; }
            .toolbar { margin: 8px; }
            .sheet {
                max-width: none;
                width: auto;
                margin: 8px;
                padding: 5mm 4mm;
            }
            /* Шапка «заказ-наряд / исполнитель» — в столбик */
            .head { flex-direction: column; gap: 8px; }
            .head .col { width: 100%; }
            /* Широкие таблицы не схлопываем — даём прокрутку внутри листа */
            .table-scroll .vehicle { min-width: 460px; }
            .table-scroll .items { min-width: 640px; }
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar">
        <a href="#" class="btn" onclick="window.print(); return false;">🖨 Печать</a>
    </div>

    <div class="sheet">

        {{-- ── Шапка: заказ-наряд / исполнитель ── --}}
        <div class="head">
            <div class="col">
                <h1>Заказ-наряд №{{ $order->id }}</h1>
                <div class="lines">
                    <div>Дата приема заказа: <span class="v">{{ $order->created_at?->format('d.m.Y') }}</span></div>
                    <div>Дата выполнения заказа: <span class="v">{{ $order->actual_finish?->format('d.m.Y') }}</span></div>
                    <div>№ гарантийного талона: <span class="v">{{ $order->id }}</span></div>
                    <div>Заказ принял: <span class="v">{{ $order->receiver?->name }}</span></div>
                </div>
            </div>
            <div class="col">
                <h1>Исполнитель</h1>
                <div class="lines">
                    <div class="v">{{ $branch?->name ?? 'Mobile 1' }}</div>
                    <div>{{ $branch ? trim(($branch->city ? $branch->city.', ' : '').$branch->address) : '' }}</div>
                    <div>ИНН: <span class="v"></span></div>
                    <div>Контактный телефон: <span class="v">{{ $branch?->phone }}</span></div>
                </div>
            </div>
        </div>

        {{-- ── Заказчик ── --}}
        <h2>Заказчик</h2>
        <div class="lines">
            <div>ФИО: <span class="v">{{ $order->client?->full_name }}</span></div>
            <div>Телефон: <span class="v">{{ $order->client?->phone }}</span></div>
        </div>

        {{-- ── Автомобиль ── --}}
        <div class="table-scroll">
        <table class="vehicle" style="margin-top:6px;">
            <tr>
                <td><div class="lbl">Марка</div><div class="val">{{ $car?->brand?->name }}</div></td>
                <td><div class="lbl">Гос. номер</div><div class="val">{{ $car?->license_plate }}</div></td>
                <td><div class="lbl">VIN</div><div class="val">{{ $car?->vin }}</div></td>
            </tr>
            <tr>
                <td><div class="lbl">Модель</div><div class="val">{{ $car?->model?->name }}</div></td>
                <td><div class="lbl">Номер кузова</div><div class="val">{{ $car?->body_number }}</div></td>
                <td rowspan="2"><div class="lbl">Описание дефектов / комментарий</div><div class="val">{{ $defects }}</div></td>
            </tr>
            <tr>
                <td><div class="lbl">Год выпуска</div><div class="val">{{ $car?->year }}</div></td>
                <td><div class="lbl">Номер двигателя</div><div class="val">{{ $car?->engine_number }}</div></td>
            </tr>
        </table>
        </div>

        {{-- ── Работы ── --}}
        <h2>Работы</h2>
        <div class="table-scroll">
        <table class="items">
            <thead>
                <tr>
                    <th style="width:26px;">№</th>
                    <th style="width:46px;">Код</th>
                    <th>Наименование работ</th>
                    <th style="width:64px;">Количество</th>
                    <th style="width:62px;">Цена, руб</th>
                    <th style="width:70px;">Сумма, руб</th>
                    <th style="width:110px;">Исполнитель</th>
                    <th style="width:70px;">Подпись</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->services as $i => $service)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td></td>
                        <td>{{ $service->name }}</td>
                        <td class="c">{{ $qty($service->pivot->quantity) }}</td>
                        <td class="r">{{ $money($service->pivot->price) }}</td>
                        <td class="r">{{ $money($service->pivot->sum) }}</td>
                        <td>{{ $executors[$service->pivot->executor_id] ?? '' }}</td>
                        <td></td>
                    </tr>
                @endforeach
                @for($n = 0; $n < $workPad; $n++)
                    <tr>
                        <td class="c">{{ $order->services->count() + $n + 1 }}</td>
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor
                <tr class="total">
                    <td colspan="5" class="lbl">Стоимость работ, руб</td>
                    <td class="box r">{{ $money($servicesSum) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
        </div>

        {{-- ── Материалы исполнителя ── --}}
        <h2>Материалы исполнителя</h2>
        <div class="table-scroll">
        <table class="items">
            <thead>
                <tr>
                    <th style="width:26px;">№</th>
                    <th style="width:46px;">Код</th>
                    <th>Наименование материала</th>
                    <th style="width:64px;">Количество</th>
                    <th style="width:62px;">Цена, руб</th>
                    <th style="width:70px;">Сумма, руб</th>
                    <th style="width:70px;">Подпись</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->parts as $i => $part)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $part->article }}</td>
                        <td>{{ $part->name }}</td>
                        <td class="c">{{ $qty($part->pivot->quantity) }}</td>
                        <td class="r">{{ $money($part->pivot->price) }}</td>
                        <td class="r">{{ $money($part->pivot->sum) }}</td>
                        <td></td>
                    </tr>
                @endforeach
                @for($n = 0; $n < $matPad; $n++)
                    <tr>
                        <td class="c">{{ $order->parts->count() + $n + 1 }}</td>
                        <td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor
                <tr class="total">
                    <td colspan="5" class="lbl">Стоимость материалов, руб</td>
                    <td class="box r">{{ $money($partsSum) }}</td>
                    <td colspan="1"></td>
                </tr>
                <tr class="total">
                    <td colspan="5" class="lbl">Итого, за работы и материалы, руб</td>
                    <td class="box r">{{ $money($grand) }}</td>
                    <td colspan="1"></td>
                </tr>
            </tbody>
        </table>
        </div>

        {{-- ── Материалы заказчика ── --}}
        <h2>Материалы заказчика</h2>
        <table class="cust">
            <thead>
                <tr>
                    <th>Наименование материала</th>
                    <th style="width:120px;">Количество</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->customerParts as $cp)
                    <tr>
                        <td>{{ $cp->name }}</td>
                        <td>{{ $qty($cp->quantity) }} {{ $cp->unit }}</td>
                    </tr>
                @endforeach
                @for($n = 0; $n < $custPad; $n++)
                    <tr><td>&nbsp;</td><td></td></tr>
                @endfor
            </tbody>
        </table>

        {{-- ── Подписи ── --}}
        <div class="foot">
            Заказ и замененные дефектные детали (остатки материалов) получил. Изделие проверено в моем присутствии.

            <div class="sign-row">
                Дата <span class="ln"></span>
            </div>
            <div class="sign-row">
                Подпись заказчика <span class="ln" style="min-width:220px;"></span> / <span class="ln"></span>
            </div>
            <div class="sign-row">
                Подпись исполнителя <span class="ln" style="min-width:210px;"></span> / <span class="ln"></span>
            </div>
        </div>

    </div>
</body>
</html>
