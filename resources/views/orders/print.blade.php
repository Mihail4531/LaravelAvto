@php
    /** @var \App\Models\Order $order */
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $servicesSum = $order->services->sum('pivot.sum');
    $partsSum = $order->parts->sum('pivot.sum');
    $car = $order->car;
    $branch = $order->branch;
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заказ-наряд №{{ $order->id }}</title>
    <style>
        @page { size: A4; margin: 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            background: #f3f4f6;
        }
        .sheet {
            background: #fff;
            max-width: 210mm;
            margin: 16px auto;
            padding: 18mm 16mm;
            box-shadow: 0 1px 8px rgba(0,0,0,.12);
        }
        .toolbar {
            max-width: 210mm;
            margin: 16px auto 0;
            text-align: right;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; }
        .head .org { font-size: 11px; color: #444; }
        .head .org b { font-size: 14px; color: #1a1a1a; display: block; margin-bottom: 2px; }
        .head .doc { text-align: right; white-space: nowrap; }
        .head .doc h1 { font-size: 18px; margin: 0 0 4px; }
        .head .doc .date { font-size: 11px; color: #444; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; margin: 16px 0 6px; color: #1a1a1a; border-bottom: 1px solid #cbd1d8; padding-bottom: 3px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; }
        .row { display: grid; grid-template-columns: 150px 1fr; gap: 8px; padding: 2px 0; }
        .row .k { color: #555; }
        .row .v { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #cbd1d8; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #eef2f7; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        tfoot td { font-weight: 700; }
        .totals { margin-top: 10px; margin-left: auto; width: 280px; }
        .totals .row { grid-template-columns: 1fr auto; }
        .totals .grand { border-top: 2px solid #1a1a1a; margin-top: 4px; padding-top: 6px; font-size: 14px; }
        .note { font-size: 11px; color: #444; margin-top: 14px; }
        .sign { display: flex; justify-content: space-between; gap: 40px; margin-top: 28px; }
        .sign .col { flex: 1; }
        .sign .line { border-bottom: 1px solid #1a1a1a; height: 28px; }
        .sign .cap { font-size: 10px; color: #555; margin-top: 3px; }
        .muted { color: #888; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { box-shadow: none; margin: 0; max-width: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar">
        <a href="#" class="btn" onclick="window.print(); return false;">🖨 Печать</a>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="org">
                <b>{{ $branch?->name ?? 'Автосервис' }}</b>
                @if($branch?->address){{ $branch->city ? $branch->city.', ' : '' }}{{ $branch->address }}<br>@endif
                @if($branch?->phone)тел. {{ $branch->phone }}@endif
            </div>
            <div class="doc">
                <h1>Заказ-наряд №{{ $order->id }}</h1>
                <div class="date">от {{ $order->created_at->format('d.m.Y H:i') }}</div>
            </div>
        </div>

        <h2>Клиент и автомобиль</h2>
        <div class="grid">
            <div>
                <div class="row"><span class="k">Клиент</span><span class="v">{{ $order->client?->full_name ?: '—' }}</span></div>
                <div class="row"><span class="k">Телефон</span><span class="v">{{ $order->client?->phone ?: '—' }}</span></div>
            </div>
            <div>
                <div class="row"><span class="k">Автомобиль</span><span class="v">{{ $car ? trim(($car->brand?->name ?? '').' '.($car->model?->name ?? '')) : '—' }}</span></div>
                <div class="row"><span class="k">Гос. номер</span><span class="v">{{ $car?->license_plate ?: '—' }}</span></div>
                <div class="row"><span class="k">VIN</span><span class="v">{{ $car?->vin ?: '—' }}</span></div>
                <div class="row"><span class="k">Год / цвет</span><span class="v">{{ $car?->year ?: '—' }}{{ $car?->color ? ' · '.$car->color : '' }}</span></div>
            </div>
        </div>

        <h2>Приёмка</h2>
        <div class="grid">
            <div>
                <div class="row"><span class="k">Пробег при приёме</span><span class="v">{{ $order->current_mileage ? number_format($order->current_mileage, 0, '', ' ').' км' : '—' }}</span></div>
                <div class="row"><span class="k">Уровень топлива</span><span class="v">{{ $fuelLevel ?: '—' }}</span></div>
                <div class="row"><span class="k">Приёмщик</span><span class="v">{{ $order->receiver?->name ?: '—' }}</span></div>
            </div>
            <div>
                <div class="row"><span class="k">Комплектность</span><span class="v">{{ $order->equipment ?: '—' }}</span></div>
                <div class="row"><span class="k">План завершения</span><span class="v">{{ $order->planned_finish?->format('d.m.Y H:i') ?? '—' }}</span></div>
            </div>
        </div>
        @if($order->damages_on_acceptance)
            <div class="row" style="grid-template-columns:150px 1fr;margin-top:4px;"><span class="k">Повреждения</span><span class="v">{{ $order->damages_on_acceptance }}</span></div>
        @endif
        @if($order->problem_description)
            <div class="row" style="grid-template-columns:150px 1fr;"><span class="k">Жалобы клиента</span><span class="v">{{ $order->problem_description }}</span></div>
        @endif

        <h2>Работы</h2>
        @if($order->services->isEmpty())
            <p class="muted">Работы не добавлены.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:28px;">№</th>
                        <th>Наименование работы</th>
                        <th class="num" style="width:60px;">Кол-во</th>
                        <th class="num" style="width:90px;">Цена, ₽</th>
                        <th class="num" style="width:100px;">Сумма, ₽</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->services as $i => $service)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $service->name }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $service->pivot->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td class="num">{{ $money($service->pivot->price) }}</td>
                            <td class="num">{{ $money($service->pivot->sum) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="num">Итого по работам:</td><td class="num">{{ $money($servicesSum) }}</td></tr>
                </tfoot>
            </table>
        @endif

        @if($order->parts->isNotEmpty())
            <h2>Запчасти и материалы</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width:28px;">№</th>
                        <th>Наименование</th>
                        <th class="num" style="width:60px;">Кол-во</th>
                        <th class="num" style="width:90px;">Цена, ₽</th>
                        <th class="num" style="width:100px;">Сумма, ₽</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->parts as $i => $part)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $part->name }}{{ $part->article ? ' (арт. '.$part->article.')' : '' }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $part->pivot->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td class="num">{{ $money($part->pivot->price) }}</td>
                            <td class="num">{{ $money($part->pivot->sum) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" class="num">Итого по запчастям:</td><td class="num">{{ $money($partsSum) }}</td></tr>
                </tfoot>
            </table>
        @endif

        @if($order->customerParts->isNotEmpty())
            <h2>Запчасти клиента (давальческие)</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width:28px;">№</th>
                        <th>Наименование</th>
                        <th class="num" style="width:80px;">Кол-во</th>
                        <th style="width:50px;">Ед.</th>
                        <th>Примечание</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->customerParts as $i => $cp)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $cp->name }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format((float) $cp->quantity, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ $cp->unit }}</td>
                            <td>{{ $cp->note ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="note">Запчасти предоставлены клиентом. Стоимость деталей в сумму заказа не входит (оплачивается только работа). Гарантия на детали, предоставленные клиентом, сервисом не предоставляется.</p>
        @endif

        <div class="totals">
            <div class="row"><span class="k">Работы</span><span class="v">{{ $money($servicesSum) }} ₽</span></div>
            <div class="row"><span class="k">Запчасти</span><span class="v">{{ $money($partsSum) }} ₽</span></div>
            <div class="row grand"><span class="k">ИТОГО к оплате</span><span class="v">{{ $money($order->total_amount) }} ₽</span></div>
            @if($order->paid_amount > 0)
                <div class="row"><span class="k">Оплачено</span><span class="v">{{ $money($order->paid_amount) }} ₽</span></div>
                <div class="row"><span class="k">Остаток</span><span class="v">{{ $money($order->remaining_amount) }} ₽</span></div>
            @endif
        </div>

        <p class="note">
            С перечнем работ, запчастей и их стоимостью ознакомлен и согласен.
            Автомобиль на ответственное хранение передал. Претензий к комплектности
            и внешнему виду автомобиля на момент приёма не имею.
        </p>

        <div class="sign">
            <div class="col">
                <div class="line"></div>
                <div class="cap">Клиент (подпись / расшифровка)</div>
            </div>
            <div class="col">
                <div class="line"></div>
                <div class="cap">Приёмщик: {{ $order->receiver?->name ?: '' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
