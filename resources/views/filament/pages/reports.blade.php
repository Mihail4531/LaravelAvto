<x-filament-panels::page>
    {{-- Локальные стили для страницы отчётов --}}
    <style>
        .reports-date-input {
            border: 1px solid rgb(209 213 219);
            border-radius: 8px;
            padding: 9px 12px;
            width: 100%;
            background: #ffffff;
            color: #111827;
            font-size: 14px;
            font-family: inherit;
            line-height: 1.4;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .reports-date-input:focus {
            outline: none;
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .dark .reports-date-input {
            background: rgb(31 41 55);
            border-color: rgb(75 85 99);
            color: #ffffff;
            color-scheme: dark;
        }
        .reports-date-input::-webkit-calendar-picker-indicator {
            opacity: 0.6;
            cursor: pointer;
        }
        .dark .reports-date-input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.7;
        }
        .reports-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
            color: rgb(75 85 99);
        }
        .dark .reports-label {
            color: rgb(156 163 175);
        }
        .reports-period-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            align-items: end;
        }
        @media (min-width: 768px) {
            .reports-period-row {
                grid-template-columns: 1fr 1fr auto;
            }
        }
        .reports-quick-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            grid-column: 1 / -1;
        }
        @media (min-width: 768px) {
            .reports-quick-buttons {
                grid-column: auto;
            }
        }
        .reports-card-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgb(107 114 128);
            margin-bottom: 8px;
        }
        .dark .reports-card-label {
            color: rgb(156 163 175);
        }
        .reports-card-title {
            font-size: 15px;
            font-weight: 600;
            color: rgb(17 24 39);
            line-height: 1.3;
        }
        .dark .reports-card-title {
            color: #ffffff;
        }
        .reports-card-text {
            font-size: 13px;
            color: rgb(75 85 99);
            line-height: 1.55;
            margin: 0;
        }
        .dark .reports-card-text {
            color: rgb(156 163 175);
        }
        .reports-download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgb(17 24 39);
            color: white;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.15s, transform 0.1s;
        }
        .reports-download-btn:hover { background: rgb(55 65 81); }
        .reports-download-btn:active { transform: scale(0.98); }
        .dark .reports-download-btn {
            background: rgb(99 102 241);
        }
        .dark .reports-download-btn:hover {
            background: rgb(79 70 229);
        }
        .reports-badge-period {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgb(99 102 241);
            background: rgba(99, 102, 241, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .reports-badge-now {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgb(75 85 99);
            background: rgba(107, 114, 128, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .dark .reports-badge-now {
            color: rgb(156 163 175);
        }
    </style>

    @php
        $stats = $this->getStats();
        $reports = [
            [
                'route'       => 'reports.orders',
                'label'       => 'Заказы',
                'description' => 'Список заказов с клиентами, авто, суммами и статусами за период.',
                'icon'        => 'heroicon-o-document-text',
                'with_dates'  => true,
            ],
            [
                'route'       => 'reports.payments',
                'label'       => 'Платежи',
                'description' => 'Все принятые платежи за период с итоговой суммой.',
                'icon'        => 'heroicon-o-banknotes',
                'with_dates'  => true,
            ],
            [
                'route'       => 'reports.income',
                'label'       => 'Доходы по дням',
                'description' => 'Выручка по датам, количество платежей, средний чек.',
                'icon'        => 'heroicon-o-chart-bar',
                'with_dates'  => true,
            ],
            [
                'route'       => 'reports.appointments',
                'label'       => 'Заявки',
                'description' => 'Онлайн-заявки за период со статусами и связанными заказами.',
                'icon'        => 'heroicon-o-calendar-days',
                'with_dates'  => true,
            ],
            [
                'route'       => 'reports.parts',
                'label'       => 'Склад',
                'description' => 'Текущие остатки запчастей с пометкой дефицита.',
                'icon'        => 'heroicon-o-archive-box',
                'with_dates'  => false,
            ],
            [
                'route'       => 'reports.clients',
                'label'       => 'Клиенты и автомобили',
                'description' => 'Полная база клиентов и их машин со всеми данными: контакты, марка, модель, гос. номер, VIN, год, пробег, двигатель, КПП.',
                'icon'        => 'heroicon-o-users',
                'with_dates'  => false,
            ],
        ];
    @endphp

    {{-- ═════ Период ═════ --}}
    <x-filament::section>
        <x-slot name="heading">Период выгрузки</x-slot>
        <x-slot name="description">Все отчёты с пометкой "за период" будут ограничены этими датами</x-slot>

        <div class="reports-period-row">
            <div>
                <label class="reports-label">С даты</label>
                <input type="date" wire:model.live="dateFrom" class="reports-date-input">
            </div>
            <div>
                <label class="reports-label">По дату</label>
                <input type="date" wire:model.live="dateTo" class="reports-date-input">
            </div>
            <div class="reports-quick-buttons">
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="$set('dateFrom', '{{ now()->startOfMonth()->format('Y-m-d') }}'); $set('dateTo', '{{ now()->format('Y-m-d') }}')"
                >Этот месяц</x-filament::button>
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="$set('dateFrom', '{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}'); $set('dateTo', '{{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}')"
                >Прошлый месяц</x-filament::button>
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="$set('dateFrom', '{{ now()->startOfYear()->format('Y-m-d') }}'); $set('dateTo', '{{ now()->format('Y-m-d') }}')"
                >Этот год</x-filament::button>
            </div>
        </div>

        <div style="margin-top: 12px; font-size: 12px; color: rgb(107 114 128);">
            Сейчас выбрано: <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d.m.Y') }}</strong>
        </div>
    </x-filament::section>

    {{-- ═════ Сводка ═════ --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
        @php
            $cards = [
                ['Заказов за период', number_format($stats['orders'], 0, '.', ' '), '#6366f1'],
                ['Выручка', number_format($stats['income'], 0, '.', ' ') . ' ₽', '#10b981'],
                ['Заявок за период',  number_format($stats['appointments'], 0, '.', ' '), '#0ea5e9'],
                ['Позиций на складе', number_format($stats['parts_total'], 0, '.', ' '), '#64748b'],
                ['Дефицит',           number_format($stats['parts_low'], 0, '.', ' '), $stats['parts_low'] > 0 ? '#e11d48' : '#94a3b8'],
            ];
        @endphp
        @foreach($cards as [$label, $value, $accent])
        <x-filament::section>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <div class="reports-card-label">{{ $label }}</div>
                <div style="font-size:28px; font-weight:700; color:{{ $accent }}; line-height:1;">{{ $value }}</div>
            </div>
        </x-filament::section>
        @endforeach
    </div>

    {{-- ═════ Карточки отчётов ═════ --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
        @foreach($reports as $report)
        <x-filament::section>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:rgba(99, 102, 241, 0.1); display:flex; align-items:center; justify-content:center;">
                        <x-filament::icon :icon="$report['icon']" style="width:20px; height:20px; color:rgb(99 102 241);"/>
                    </div>
                    <div style="flex:1;">
                        <div class="reports-card-title">{{ $report['label'] }}</div>
                        @if($report['with_dates'])
                        <div class="reports-badge-period">За период</div>
                        @else
                        <div class="reports-badge-now">Текущее</div>
                        @endif
                    </div>
                </div>
                <p class="reports-card-text">{{ $report['description'] }}</p>
                <a
                    href="{{ route($report['route']) }}{{ $report['with_dates'] ? '?from=' . $dateFrom . '&to=' . $dateTo : '' }}"
                    target="_blank"
                    rel="noopener"
                    class="reports-download-btn"
                >
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:16px; height:16px;"/>
                    Скачать XLSX
                </a>
            </div>
        </x-filament::section>
        @endforeach
    </div>

    {{-- Подсказка --}}
    <x-filament::section>
        <div style="display:flex; gap:12px; align-items:flex-start;">
            <x-filament::icon icon="heroicon-o-information-circle" style="width:20px; height:20px; color:rgb(99 102 241); flex-shrink:0; margin-top:2px;"/>
            <div class="reports-card-text">
                Файлы открываются в Excel, Numbers, LibreOffice Calc и Google Sheets. Заголовки закреплены — удобно листать. Можно делать сводные таблицы и диаграммы прямо в Excel.
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
