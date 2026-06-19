@extends('layouts.public')

@section('title', 'Mobile 1')

@section('content')

{{-- Индикатор прокрутки — «датчик» (ширина = % прокрутки страницы) --}}
<div id="scroll-gauge-track" aria-hidden="true"></div>
<div id="scroll-gauge" aria-hidden="true"></div>

{{-- ═══════════════════════════════════════════════════════════════════
                              HERO — светлый «стальной» сплит
═══════════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden bg-gradient-to-b from-ink-800 to-ink-900 pt-14 lg:pt-20 pb-16 lg:pb-24">

    {{-- тонкая верхняя кромка-акцент --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-ink-600 to-transparent"></div>

    <div class="relative max-w-[1400px] mx-auto px-5 lg:px-10">
        <div class="flex flex-col gap-8 sm:gap-10 lg:grid lg:grid-cols-12 lg:gap-14 lg:items-center">

            {{-- Левая колонна: текст --}}
            <div class="col-span-12 lg:col-span-7">

                {{-- Бейдж статуса — отражает реальное наличие свободных окон на сегодня --}}
                <div class="animate-snap stagger-1 mb-7">
                    @if ($hasSlotsToday)
                        <span class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-ink-700 bg-ink-800 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                            </span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-ink-300">Открыто · свободные окна сегодня</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-ink-700 bg-ink-800 shadow-sm">
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-ink-500"></span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-ink-400">На сегодня свободных окон нет</span>
                        </span>
                    @endif
                </div>

                {{-- Заголовок --}}
                <h1 class="font-display font-extrabold tracking-[-0.03em] leading-[0.95] text-[clamp(2rem,7vw,4.75rem)] text-ink-100">
                    <span class="block animate-snap stagger-1">Точный ремонт</span>
                    <span class="block animate-snap stagger-2 text-primary-500">без компромиссов</span>
                </h1>

                <p class="mt-6 max-w-xl text-ink-300 text-[17px] lg:text-[19px] leading-relaxed animate-snap stagger-3 text-pretty">
                    Современная диагностика, опытные мастера и прозрачные цены.
                    Запишитесь онлайн за две минуты — выберите услуги и удобное время.
                </p>

                <div class="mt-9 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 sm:gap-4 animate-snap stagger-4">
                    <a href="{{ route('booking') }}" class="btn-primary group w-full sm:w-auto justify-center sm:justify-start">
                        Записаться онлайн
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="{{ route('lookup.form') }}" class="btn-ghost w-full sm:w-auto justify-center sm:justify-start">
                        Моя история
                    </a>
                </div>

                {{-- Часы работы — спокойная подпись, без крупных метрик --}}
                <div class="mt-8 inline-flex items-center gap-2.5 text-[13px] text-ink-400 animate-snap stagger-5">
                    <svg class="w-4 h-4 text-ink-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Пн–Сб 9:00–21:00</span>
                </div>
            </div>

            {{-- Правая колонна: карточка онлайн-записи (панель проявляется, содержимое всплывает по очереди) --}}
            <aside class="col-span-12 lg:col-span-5">
                <div class="card-steel edge-top relative p-6 sm:p-7 lg:p-8 hero-panel">

                    <div class="flex items-center gap-3 mb-6 hero-rise" style="--i:0">
                        <span class="icon-bubble !w-10 !h-10">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span class="eyebrow-muted">Онлайн-запись</span>
                    </div>

                    <h2 class="font-display font-extrabold text-ink-100 text-[26px] lg:text-[30px] leading-[1.05] tracking-tight mb-7 hero-rise" style="--i:1">
                        Запишитесь<br>за две минуты
                    </h2>

                    <ul class="space-y-3.5 mb-8">
                        @foreach([
                            'Запись онлайн без звонка',
                            'Выбор услуг и удобного времени',
                            'Гарантия на работы 6 месяцев',
                        ] as $point)
                        <li class="flex items-start gap-3 text-ink-200 text-[14px] leading-snug hero-rise" style="--i:{{ $loop->index + 2 }}">
                            <span class="mt-0.5 shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path class="hero-check" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('booking') }}" class="btn-primary group w-full justify-center hero-rise" style="--i:5">
                        Выбрать время
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>

                    @if($contact->phone)
                    <div class="mt-6 pt-6 border-t border-ink-700 flex items-center justify-between hero-rise" style="--i:6">
                        <span class="text-ink-400 text-[13px]">Или позвоните</span>
                        <a href="{{ $contact->telHref() }}" class="font-mono text-[15px] text-ink-100 hover:text-primary-500 transition-colors">{{ $contact->phone }}</a>
                    </div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- Бегущая строка — «приборная полка» под hero --}}
<div class="marquee-wrap border-y border-ink-700 bg-ink-800 overflow-hidden relative">
    <div class="py-5 flex items-center overflow-hidden whitespace-nowrap">
        <div class="flex gap-12 animate-marquee shrink-0">
            @php
                $ticker = ['Диагностика', 'Кузовной ремонт', 'Двигатель и КПП', 'Тормозная система', 'Ходовая часть', 'Электрика', 'ТО и обслуживание', 'Шиномонтаж', 'Развал-схождение', 'Кондиционер'];
            @endphp
            @foreach(array_merge($ticker, $ticker, $ticker) as $item)
            <span class="flex items-center gap-3 text-ink-400 text-[14px] font-medium shrink-0 px-6">
                <span class="w-1.5 h-1.5 bg-primary-500 rounded-full shrink-0"></span>
                {{ $item }}
            </span>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
                           СТАТИСТИКА
═══════════════════════════════════════════════════════════════════ --}}
@php
    $branchCount = $branches->count();
    $branchLabel = 'филиал' . ($branchCount == 1 ? '' : ($branchCount < 5 ? 'а' : 'ов'));
    $serviceCount = \App\Models\Service::where('active', true)->count();
    $gaugeLen = 245.044; // длина 270°-дуги при r=52 (C = 326.726)
    $stats = [
        ['num' => 8,             'suffix' => '',  'frac' => .78, 'l1' => 'лет работы',       'l2' => 'на рынке услуг'],
        ['num' => 5000,          'suffix' => '+', 'frac' => .92, 'l1' => 'обслуженных авто', 'l2' => 'постоянные клиенты'],
        ['num' => $serviceCount, 'suffix' => '',  'frac' => .72, 'l1' => 'видов услуг',      'l2' => 'полный цикл работ'],
        ['num' => $branchCount,  'suffix' => '',  'frac' => .60, 'l1' => $branchLabel . ' в городе', 'l2' => $branchCount == 1 ? 'удобное расположение' : 'удобные локации'],
    ];
@endphp
<section class="bg-ink-900 py-14 sm:py-20 lg:py-28">

    {{-- Один общий градиент дуги для всех датчиков (id глобален в документе) --}}
    <svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="gaugeGrad" x1="0" y1="0" x2="1" y2="1">
                <stop class="g0" offset="0"/>
                <stop class="g1" offset="1"/>
            </linearGradient>
        </defs>
    </svg>

    <div class="max-w-[1400px] mx-auto px-5 lg:px-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
            @foreach($stats as $i => $stat)
            @php $off = $stat['num'] > 0 ? round($gaugeLen * (1 - $stat['frac']), 2) : $gaugeLen; @endphp
            <div class="bg-ink-800 p-4 sm:p-6 lg:p-8 border border-ink-700 hover-lift edge-top flex flex-col items-center text-center" data-reveal data-reveal-delay="{{ min($i + 1, 4) }}">

                {{-- Круговой датчик-спидометр (270°, разрыв снизу) — ширина текучая,
                     чтобы не выпирать из узкой карточки на малых экранах --}}
                <div class="stat-gauge relative w-full max-w-[112px] sm:max-w-[128px] mb-4 sm:mb-5">
                    <svg viewBox="0 0 120 120" class="block w-full" aria-hidden="true">
                        {{-- фоновая шкала --}}
                        <circle cx="60" cy="60" r="52" fill="none" stroke="var(--color-ink-700)" stroke-width="7"
                                stroke-linecap="round" stroke-dasharray="245.044 326.726" transform="rotate(135 60 60)"/>
                        {{-- активная дуга --}}
                        <circle class="stat-arc" cx="60" cy="60" r="52" fill="none" stroke="url(#gaugeGrad)" stroke-width="7"
                                stroke-linecap="round" stroke-dasharray="245.044 326.726" transform="rotate(135 60 60)"
                                style="--len:{{ $gaugeLen }}; --off:{{ $off }}"/>
                        {{-- стрелка спидометра: старт 225°, ход 270° по дуге; угол ведёт JS
                             синхронно с дугой и счётом числа (см. initOdometer) --}}
                        <g class="stat-needle" style="--a0:225; --span:270">
                            <line class="stat-needle-l" x1="60" y1="60" x2="60" y2="20"/>
                        </g>
                        <circle class="stat-hub" cx="60" cy="60" r="5"/>
                        <circle class="stat-hub-dot" cx="60" cy="60" r="2"/>
                    </svg>
                    {{-- число в нижней части циферблата — как на спидометре, ниже стрелки и ступицы.
                         Без разделителя разрядов: «5000+» компактнее «5 000+» и влезает в узкую нижнюю хорду --}}
                    <div class="absolute inset-x-0 bottom-0 flex justify-center pb-[18%] px-2">
                        <span class="font-display font-extrabold text-amber-gradient num-tabular leading-none tracking-tight whitespace-nowrap text-[clamp(1.2rem,2.7vw,1.5rem)]"
                              @if($stat['num'] > 0) data-odometer="{{ $stat['num'] }}" data-decimals="0" data-group="0" data-suffix="{{ $stat['suffix'] }}" @endif>{{ $stat['num'] > 0 ? number_format($stat['num'], 0, '.', '') . $stat['suffix'] : '—' }}</span>
                    </div>
                </div>

                <div class="font-semibold text-ink-100 text-[16px] mb-1">{{ $stat['l1'] }}</div>
                <div class="text-ink-400 text-[14px]">{{ $stat['l2'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════
                           УСЛУГИ
═══════════════════════════════════════════════════════════════════ --}}
<section id="services" class="bg-ink-900 py-16 sm:py-24 lg:py-32 relative overflow-hidden">

    <div class="glow w-[500px] h-[500px] top-40 -right-32 bg-primary-500/15 animate-blob"></div>

    <div class="relative max-w-[1400px] mx-auto px-5 lg:px-10">

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12 lg:mb-16" data-reveal>
            <div class="max-w-2xl">
                <span class="eyebrow mb-4 block">Каталог услуг</span>
                <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4rem)]">
                    Полный цикл работ<br>
                    <span class="text-ink-400">для любого авто</span>
                </h2>
            </div>
            <p class="text-ink-400 text-[15px] leading-relaxed max-w-md">
                Выберите категорию, чтобы увидеть актуальные услуги и цены.
             
            </p>
        </div>

        <livewire:category-list />

    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════
                        КАК ЭТО РАБОТАЕТ
═══════════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="bg-ink-900 py-16 sm:py-24 lg:py-32">
    <div class="max-w-[1400px] mx-auto px-5 lg:px-10">

        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 lg:mb-20" data-reveal>
            <span class="eyebrow mb-4 block">Процесс</span>
            <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4rem)]">
                От заявки до выезда<br>
                <span class="text-amber-gradient">в четыре шага</span>
            </h2>
            <p class="mt-6 text-ink-400 text-[16px] leading-relaxed">
                Никаких звонков и ожиданий — всё прозрачно онлайн.
            </p>
        </div>

        @php
            $steps = [
                ['icon' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z', 'title' => 'Выбираете услуги', 'desc' => 'Из удобного каталога с ценами и описанием каждой работы. Можно записать сразу несколько услуг.'],
                ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => $branchCount > 1 ? 'Время и филиал' : 'Выбираете время', 'desc' => $branchCount > 1 ? 'Выбираете ближайший филиал и свободное окно. Календарь показывает только реальные слоты.' : 'Выбираете свободное окно в календаре. Показываем только реальные слоты.'],
                ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'title' => 'Указываете контакты', 'desc' => 'Имя и телефон для связи. Email — для доступа к истории обслуживания.'],
                ['icon' => 'M5 13l4 4L19 7', 'title' => 'Готово', 'desc' => 'Заявка с услугами и выбранным временем передаётся в сервис.'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
            @foreach($steps as $i => $step)
            <div class="card-steel relative group p-7 lg:p-8" data-reveal data-reveal-delay="{{ min($i + 1, 4) }}">
                {{-- Номер шага --}}
                <div class="absolute top-6 right-6 font-mono text-[11px] text-ink-300 num-tabular tracking-widest">
                    0{{ $i + 1 }} / 04
                </div>

                <div class="icon-bubble mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                    </svg>
                </div>

                <h3 class="font-display font-bold text-xl text-ink-100 tracking-tight mb-3 leading-tight">
                    {{ $step['title'] }}
                </h3>
                <p class="text-ink-400 text-[14px] leading-relaxed">
                    {{ $step['desc'] }}
                </p>
            </div>
            @endforeach
        </div>

        <div class="mt-12 lg:mt-16 text-center">
            <a href="{{ route('booking') }}" class="btn-primary inline-flex group">
                Записаться сейчас
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════
                         ПРЕИМУЩЕСТВА
═══════════════════════════════════════════════════════════════════ --}}
<section id="advantages" class="bg-ink-900 text-ink-100 py-16 sm:py-24 lg:py-32 relative overflow-hidden">

    <div class="glow w-[600px] h-[600px] -bottom-40 -left-32 bg-primary-600/20 animate-blob"></div>
    <div class="glow w-[400px] h-[400px] top-20 right-10 bg-primary-500/10 animate-blob" style="animation-delay: -10s"></div>

    <div class="relative max-w-[1400px] mx-auto px-5 lg:px-10">

        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-12 lg:gap-12 mb-16 lg:mb-20" data-reveal>
            <div class="col-span-12 lg:col-span-7">
                <span class="eyebrow !text-primary-400 mb-4 block">О нашем сервисе</span>
                <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4.5rem)] text-ink-100">
                    Почему вы захотите<br>
                    вернуться <span class="text-amber-gradient">именно к нам</span>
                </h2>
            </div>
            <div class="col-span-12 lg:col-span-5 lg:pt-6">
                <p class="text-ink-300 text-[16px] leading-relaxed max-w-md lg:ml-auto">
                    Ваш автомобиль — не просто механизм, а важная часть вашего ритма жизни.
                </p>
            </div>
        </div>

        {{-- Bento-сетка преимуществ: единый «стальной» язык карточек --}}
        <div class="grid grid-cols-12 gap-4 lg:gap-6">

            {{-- ── Флагман: Гарантия (крупная карточка с метрикой) ─────────── --}}
            <article class="card-steel edge-top relative group col-span-12 lg:col-span-7 lg:row-span-2 p-6 sm:p-8 lg:p-12 flex flex-col"
                     data-reveal data-reveal-delay="1">
                <div class="glow w-72 h-72 -top-12 -right-12 bg-primary-600/25"></div>
                <span class="absolute top-7 right-7 font-mono text-[11px] text-ink-400 num-tabular tracking-widest">01 / 05</span>

                <div class="relative">
                    <div class="icon-bubble !w-16 !h-16 mb-8">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                        </svg>
                    </div>

                    <h3 class="font-display font-extrabold text-3xl lg:text-5xl text-ink-100 tracking-tight leading-[1.05] mb-5">
                        Гарантия<br>на все работы
                    </h3>
                    <p class="text-ink-300 text-[15px] lg:text-base leading-relaxed max-w-md">
                        Если в течение 6 месяцев после ремонта возникнет проблема по нашей вине — устраним
                        бесплатно.Команда с опытом 8+ лет.
                    </p>
                </div>

                {{-- Метрика, прижата к низу карточки --}}
                <div class="relative mt-10 lg:mt-auto pt-9 flex items-end gap-5 border-t border-ink-700/70">
                    <span class="font-display font-extrabold text-6xl lg:text-7xl leading-none num-tabular text-amber-gradient">6</span>
                    <div class="pb-1.5">
                        <div class="font-mono text-[11px] tracking-widest uppercase text-primary-400 mb-1">месяцев</div>
                        <div class="text-ink-300 text-sm">официальной гарантии</div>
                    </div>
                </div>
            </article>

            {{-- ── Прозрачные сроки ────────────────────────────────────────── --}}
            <article class="card-steel edge-top relative group col-span-12 sm:col-span-6 lg:col-span-5 p-7 lg:p-8"
                     data-reveal data-reveal-delay="2">
                <span class="absolute top-6 right-6 font-mono text-[11px] text-ink-400 num-tabular tracking-widest">02 / 05</span>
                <div class="icon-bubble mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-ink-100 tracking-tight mb-3 leading-tight">Прозрачные сроки</h3>
                <p class="text-ink-400 text-[14px] leading-relaxed">Заранее называем точное время. Если задерживаемся — звоним сами, а не клиент нам.</p>
            </article>

            {{-- ── Цена = смета ────────────────────────────────────────────── --}}
            <article class="card-steel edge-top relative group col-span-12 sm:col-span-6 lg:col-span-5 p-7 lg:p-8"
                     data-reveal data-reveal-delay="3">
                <span class="absolute top-6 right-6 font-mono text-[11px] text-ink-400 num-tabular tracking-widest">03 / 05</span>
                <div class="icon-bubble mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-ink-100 tracking-tight mb-3 leading-tight">Цена = смета</h3>
                <p class="text-ink-400 text-[14px] leading-relaxed">Согласовываем смету ДО начала работ. В итоговом счёте никаких «вдруг ещё что-то».</p>
            </article>

            {{-- ── Современное оборудование ────────────────────────────────── --}}
            <article class="card-steel edge-top relative group col-span-12 sm:col-span-6 lg:col-span-4 p-7 lg:p-8"
                     data-reveal data-reveal-delay="1">
                <span class="absolute top-6 right-6 font-mono text-[11px] text-ink-400 num-tabular tracking-widest">04 / 05</span>
                <div class="icon-bubble mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-ink-100 tracking-tight mb-3 leading-tight">Современное оборудование</h3>
                <p class="text-ink-400 text-[14px] leading-relaxed">Стенд Hunter HawkEye Elite, диагностика всех ЭБУ, профессиональный покрасочный бокс.</p>
            </article>

            {{-- ── Опытные мастера ─────────────────────────────────────────── --}}
            <article class="card-steel edge-top relative group col-span-12 sm:col-span-6 lg:col-span-4 p-7 lg:p-8"
                     data-reveal data-reveal-delay="2">
                <span class="absolute top-6 right-6 font-mono text-[11px] text-ink-400 num-tabular tracking-widest">05 / 05</span>
                <div class="icon-bubble mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-ink-100 tracking-tight mb-3 leading-tight">Опытные мастера</h3>
                <p class="text-ink-400 text-[14px] leading-relaxed">Команда с опытом 8+ лет. Каждый специалист — на своей зоне ответственности, никаких «попробуем».</p>
            </article>

            {{-- ── Акцентная CTA-карточка ──────────────────────────────────── --}}
            <article class="relative group col-span-12 sm:col-span-6 lg:col-span-4 p-7 lg:p-8 rounded-[4px] overflow-hidden text-white flex flex-col"
                     style="background: linear-gradient(150deg, var(--color-primary-500) 0%, var(--color-primary-700) 100%); box-shadow: 0 26px 55px -24px rgba(0,102,179,.65);"
                     data-reveal data-reveal-delay="3">
                <div class="absolute -top-10 -right-10 w-44 h-44 bg-white/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                {{-- скользящий блик по диагонали --}}
                <div class="absolute inset-0 opacity-[0.07] pointer-events-none"
                     style="background: repeating-linear-gradient(115deg, #fff 0 1px, transparent 1px 22px);"></div>

                <div class="relative flex items-center gap-2.5 mb-6">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white/70"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                    <span class="font-mono text-[11px] tracking-widest uppercase text-white/80">Свободно сегодня</span>
                </div>

                <h3 class="relative font-display font-extrabold text-2xl lg:text-[28px] tracking-tight leading-tight mb-3">
                    Запишитесь<br>на удобное время
                </h3>
                <p class="relative text-[14px] leading-relaxed text-white/85 mb-8">Есть свободные окна на диагностику и ТО — занимайте онлайн за минуту.</p>

                <a href="{{ route('booking') }}"
                   class="relative mt-auto inline-flex items-center gap-2 font-bold text-[13px] uppercase tracking-wider">
                    <span>Занять окно</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </article>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════
                            ГАЛЕРЕЯ
═══════════════════════════════════════════════════════════════════ --}}
@if($gallery->isNotEmpty())
<section id="gallery" class="bg-ink-900 py-16 sm:py-24 lg:py-32">
    <div class="max-w-[1400px] mx-auto px-5 lg:px-10">

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12 lg:mb-16" data-reveal>
            <div>
                <span class="eyebrow mb-4 block">Витрина</span>
                <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4rem)]">
                    Наш сервис<br>
                    <span class="text-amber-gradient">в кадре</span>
                </h2>
            </div>
            <p class="text-ink-400 text-[15px] leading-relaxed max-w-md">
                Оборудование, рабочие зоны, мастера за делом. Нажмите на любое фото — откроется крупно.
            </p>
        </div>

        <div x-data="{
                open: false,
                current: 0,
                slide: 0,
                items: @js($gallery->map(fn($g) => [
                    'id'      => $g->id,
                    'url'     => asset('storage/'.$g->image),
                    'title'   => $g->title,
                    'caption' => $g->caption,
                ])->values()),
                openAt(i) { this.current = i; this.open = true; document.body.style.overflow = 'hidden'; },
                close() { this.open = false; document.body.style.overflow = ''; },
                prev() { this.current = (this.current - 1 + this.items.length) % this.items.length; },
                next() { this.current = (this.current + 1) % this.items.length; },
                syncSlide(el) {
                    const center = el.scrollLeft + el.clientWidth / 2;
                    this.slide = [...el.children].reduce((best, c, i) => {
                        const d = Math.abs(c.offsetLeft + c.offsetWidth / 2 - center);
                        return d < best.d ? { d, i } : best;
                    }, { d: Infinity, i: 0 }).i;
                },
                goTo(i) { this.$refs.slider.children[i].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }); },
                slidePrev() { this.goTo(Math.max(0, this.slide - 1)); },
                slideNext() { this.goTo(Math.min(this.items.length - 1, this.slide + 1)); },
             }"
             @keydown.escape.window="open && close()"
             @keydown.arrow-left.window="open && prev()"
             @keydown.arrow-right.window="open && next()">

            {{-- ── Мобильный слайдер (до sm) ───────────────────────────────
                 Кадр показывается ЦЕЛИКОМ (object-contain) поверх размытой
                 копии самого себя — ничего не обрезается, поля заполнены
                 атмосферно. Снап-прокрутка, рабочие стрелки, точки. Соседние
                 кадры приглушены — даёт глубину и подсказывает «листай». --}}
            <div class="sm:hidden">
                <div class="-mx-5">
                    <div x-ref="slider" @scroll.passive="syncSlide($el)"
                         class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-px-5 px-5 pb-1 hide-scrollbar">
                        @foreach($gallery as $i => $item)
                            <button type="button" @click="openAt({{ $i }})"
                                    :class="slide === {{ $i }} ? 'opacity-100' : 'opacity-45'"
                                    class="group relative shrink-0 w-[84vw] max-w-[22rem] aspect-[4/5] snap-center overflow-hidden rounded-md bg-black ring-1 ring-ink-700 transition-opacity duration-500">
                                {{-- размытая подложка из того же кадра — заполняет поля без обрезки --}}
                                <img src="{{ asset('storage/'.$item->image) }}" alt="" aria-hidden="true" loading="lazy"
                                     class="absolute inset-0 w-full h-full object-cover scale-125 blur-2xl opacity-50">
                                {{-- сам кадр целиком --}}
                                <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title ?? '' }}" loading="lazy"
                                     class="relative z-10 w-full h-full object-contain">
                                @if($item->title)
                                <div class="absolute inset-x-0 bottom-0 z-20 p-4 pt-12 text-left bg-gradient-to-t from-black/85 via-black/35 to-transparent">
                                    <div class="font-display font-bold text-white text-[15px] leading-tight">{{ $item->title }}</div>
                                </div>
                                @endif
                                <div class="absolute top-3 right-3 z-20 w-9 h-9 bg-primary-500 rounded-full flex items-center justify-center shadow-lg shadow-black/30">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Панель управления: стрелки + точки (вынесены под слайдер, гарантированно кликабельны) --}}
                <div class="mt-7 flex items-center justify-center gap-5" x-show="items.length > 1">
                    <button type="button" @click="slidePrev()" :disabled="slide === 0" aria-label="Предыдущее фото"
                            class="shrink-0 w-11 h-11 flex items-center justify-center rounded-full border border-ink-600 text-ink-200 transition enabled:hover:border-primary-500 enabled:hover:text-primary-500 enabled:active:scale-90 disabled:opacity-30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div class="flex items-center gap-2">
                        @foreach($gallery as $i => $item)
                            <button type="button" @click="goTo({{ $i }})" aria-label="Фото {{ $i + 1 }}"
                                    class="h-1.5 rounded-full transition-all duration-300"
                                    :class="slide === {{ $i }} ? 'w-7 bg-primary-500' : 'w-1.5 bg-ink-600'"></button>
                        @endforeach
                    </div>

                    <button type="button" @click="slideNext()" :disabled="slide === items.length - 1" aria-label="Следующее фото"
                            class="shrink-0 w-11 h-11 flex items-center justify-center rounded-full border border-ink-600 text-ink-200 transition enabled:hover:border-primary-500 enabled:hover:text-primary-500 enabled:active:scale-90 disabled:opacity-30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Бенто-сетка (с sm) --}}
            <div class="hidden sm:grid sm:grid-cols-12 sm:auto-rows-[200px] lg:auto-rows-[240px] gap-3 lg:gap-4">
                @foreach($gallery as $i => $item)
                    @php
                        $spanClass = match($item->size) {
                            \App\Models\GalleryItem::SIZE_WIDE => 'sm:col-span-8 sm:row-span-1',
                            \App\Models\GalleryItem::SIZE_TALL => 'sm:col-span-4 sm:row-span-2',
                            default                            => 'sm:col-span-4 sm:row-span-1',
                        };
                    @endphp
                    <button type="button" @click="openAt({{ $i }})"
                            class="group relative overflow-hidden rounded-sm bg-ink-950 ring-1 ring-ink-700 {{ $spanClass }}">
                        <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->title ?? '' }}" loading="lazy"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-[800ms] ease-[cubic-bezier(.16,1,.3,1)] group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5 flex flex-col justify-end">
                            @if($item->title)
                            <div class="font-display font-bold text-white text-[15px] lg:text-[18px] leading-tight opacity-0 group-hover:opacity-100 translate-y-3 group-hover:translate-y-0 transition-all duration-500">
                                {{ $item->title }}
                            </div>
                            @endif
                        </div>
                        <div class="absolute top-3 right-3 w-9 h-9 bg-primary-500 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 scale-90 group-hover:scale-100 shadow-lg shadow-black/30">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- ── Лайтбокс ─────────────────────────────────────────────────
                 Закрытие — кликом по фону (@click.self): стрелки и фото —
                 прямые потомки фона, поэтому тап по ним закрытие НЕ вызывает
                 (раньше @click.outside на внутреннем блоке гасил окно при
                 нажатии стрелки). Текст белый — фон чёрный. --}}
            <div x-show="open" x-cloak @click.self="close()"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] bg-black/92 backdrop-blur-sm flex items-center justify-center select-none">

                <button type="button" @click="close()" aria-label="Закрыть"
                        class="absolute top-4 right-4 lg:top-6 lg:right-6 z-20 w-12 h-12 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <button type="button" @click="prev()" x-show="items.length > 1" aria-label="Предыдущее фото"
                        class="absolute left-2 sm:left-4 lg:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 active:scale-90 rounded-full transition">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
                </button>

                <button type="button" @click="next()" x-show="items.length > 1" aria-label="Следующее фото"
                        class="absolute right-2 sm:right-4 lg:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 active:scale-90 rounded-full transition">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
                </button>

                <div class="relative z-10 flex flex-col items-center max-w-[92vw] max-h-[92vh]">
                    <div class="relative flex items-center justify-center">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <img x-show="idx === current"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-[0.97]"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 :src="item.url" :alt="item.title || ''"
                                 class="max-w-[92vw] max-h-[78vh] object-contain shadow-2xl shadow-black/50">
                        </template>
                    </div>

                    <div class="mt-5 lg:mt-6 text-center max-w-2xl px-4">
                        <div class="font-mono text-[11px] text-white/40 num-tabular tracking-widest mb-2">
                            <span x-text="String(current + 1).padStart(2,'0')"></span> / <span x-text="String(items.length).padStart(2,'0')"></span>
                        </div>
                        <div x-show="items[current]?.title" x-text="items[current]?.title"
                             class="font-display font-bold text-white text-lg lg:text-2xl tracking-tight mb-2"></div>
                        <div x-show="items[current]?.caption" x-text="items[current]?.caption"
                             class="text-white/65 text-[14px] leading-relaxed"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════════════════
                          ФИЛИАЛЫ
═══════════════════════════════════════════════════════════════════ --}}
@if($branches->isNotEmpty())
<section id="branches" class="bg-ink-900 py-16 sm:py-24 lg:py-32">
    <div class="max-w-[1400px] mx-auto px-5 lg:px-10">

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12 lg:mb-16" data-reveal>
            <div>
                <span class="eyebrow mb-4 block">Контакты</span>
                <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4rem)]">
                    Где нас<br>
                    <span class="text-amber-gradient">найти</span>
                </h2>
            </div>
            <p class="text-ink-400 text-[15px] leading-relaxed max-w-md">
                {{ $branches->count() }} {{ $branches->count() == 1 ? 'филиал' : ($branches->count() < 5 ? 'филиала' : 'филиалов') }}
                в городе. Кликните на адрес — мы покажем филиал на карте.
            </p>
        </div>

        <div class="bg-ink-800 rounded-none border border-ink-700 overflow-hidden">
            <div class="grid grid-cols-12">

                {{-- Список филиалов --}}
                <div class="col-span-12 lg:col-span-5 lg:border-r border-ink-700">
                    <ul class="divide-y divide-ink-700">
                        @foreach($branches as $i => $branch)
                        <li>
                            <button type="button" data-branch-id="{{ $branch->id }}"
                                    class="branch-trigger group w-full text-left px-5 lg:px-7 py-6 lg:py-8 flex items-start gap-5 hover:bg-ink-900 transition-colors">
                                <div class="shrink-0 w-10 h-10 bg-ink-700 group-hover:bg-primary-500 text-ink-200 group-hover:text-white flex items-center justify-center font-mono text-[13px] font-bold transition-colors">
                                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-display font-bold text-lg text-ink-100 tracking-tight mb-2 group-hover:text-primary-400 transition-colors">
                                        {{ $branch->name }}
                                    </h3>
                                    <div class="space-y-1.5 text-[14px] text-ink-400 leading-snug">
                                        @if($branch->address)
                                        <div>{{ $branch->city ? $branch->city . ', ' : '' }}{{ $branch->address }}</div>
                                        @endif
                                        <div class="flex flex-wrap gap-x-5 gap-y-1 text-[13px]">
                                            @if($branch->phone)<span class="font-mono text-ink-200">{{ $branch->phone }}</span>@endif
                                            @if($branch->work_schedule)<span>{{ $branch->work_schedule }}</span>@endif
                                        </div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-ink-300 group-hover:text-primary-500 group-hover:translate-x-1 transition-all shrink-0 mt-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Карта --}}
                <div class="col-span-12 lg:col-span-7">
                    <div id="branches-map" class="w-full h-[420px] lg:h-[600px] bg-ink-800"></div>
                </div>

            </div>
        </div>
    </div>
</section>

@php
    $branchData = $branches->map(function ($b) {
        return [
            'id'      => $b->id,
            'name'    => $b->name,
            'address' => $b->city ? $b->city . ', ' . $b->address : ($b->address ?? ''),
            'lat'     => $b->latitude,
            'lng'     => $b->longitude,
            'phone'   => $b->phone,
        ];
    })->values();
@endphp
<script>
(function() {
    let attempts = 0;
    const start = () => {
        if (typeof ymaps === 'undefined') {
            if (++attempts > 100) { console.error('[map] Яндекс.Карты не загрузились'); return; }
            setTimeout(start, 100);
            return;
        }
        ymaps.ready(init);
    };

    const init = () => {
        const container = document.getElementById('branches-map');
        if (!container) return;

        const branches = @json($branchData);
        const valid = branches.filter(b =>
            b.lat !== null && b.lng !== null &&
            !isNaN(parseFloat(b.lat)) && !isNaN(parseFloat(b.lng)) &&
            parseFloat(b.lat) !== 0 && parseFloat(b.lng) !== 0
        );

        const center = valid.length > 0
            ? [parseFloat(valid[0].lat), parseFloat(valid[0].lng)]
            : [55.7558, 37.6173];

        const map = new ymaps.Map(container, {
            center: center,
            zoom: 10,
            controls: ['zoomControl'],
        }, {
            yandexMapDisablePoiInteractivity: true,
        });

        map.behaviors.disable('scrollZoom');

        const pinLayout = ymaps.templateLayoutFactory.createClass(
            '<div style="position:relative;width:32px;height:40px;left:-16px;top:-40px;cursor:pointer;transition:transform .2s" ' +
            'onmouseover="this.style.transform=\'scale(1.15)\'" onmouseout="this.style.transform=\'scale(1)\'">' +
            '<svg viewBox="0 0 32 40" width="32" height="40" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M16 0C7.16 0 0 7.16 0 16c0 12 16 24 16 24s16-12 16-24C32 7.16 24.84 0 16 0z" fill="#0E0C0A"/>' +
            '<circle cx="16" cy="16" r="6" fill="#E97A4B"/></svg></div>'
        );

        const markers = {};
        valid.forEach(b => {
            const balloonHtml =
                '<div class="ya-balloon">' +
                '<div class="ya-balloon__title">' + b.name + '</div>' +
                '<div class="ya-balloon__address">' + (b.address || '') + '</div>' +
                (b.phone ? '<div class="ya-balloon__phone">' + b.phone + '</div>' : '') +
                '</div>';

            const placemark = new ymaps.Placemark([parseFloat(b.lat), parseFloat(b.lng)], {
                balloonContent: balloonHtml,
                hintContent: b.name,
            }, {
                iconLayout: pinLayout,
                iconShape: {
                    type: 'Rectangle',
                    coordinates: [[-16, -40], [16, 0]],
                },
            });

            map.geoObjects.add(placemark);
            markers[b.id] = placemark;
        });

        if (valid.length > 1) {
            map.setBounds(map.geoObjects.getBounds(), {
                checkZoomRange: true,
                zoomMargin: 50,
                duration: 0,
            });
        }

        document.querySelectorAll('.branch-trigger').forEach(el => {
            el.addEventListener('click', () => {
                const p = markers[el.dataset.branchId];
                if (p) {
                    map.panTo(p.geometry.getCoordinates(), { flying: true, duration: 800 });
                    map.setZoom(14, { duration: 800 });
                    setTimeout(() => p.balloon.open(), 500);
                }
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
@endif


{{-- ═══════════════════════════════════════════════════════════════════
                          FINAL CTA
═══════════════════════════════════════════════════════════════════ --}}
<section class="bg-ink-900 py-16 sm:py-24 lg:py-32 relative overflow-hidden">
    <div class="glow w-[600px] h-[600px] -top-32 right-0 bg-primary-600/25 animate-blob"></div>
    <div class="glow w-[400px] h-[400px] bottom-0 -left-32 bg-primary-200/30 animate-blob" style="animation-delay: -6s"></div>

    <div class="relative max-w-[1400px] mx-auto px-5 lg:px-10">
        <div class="bg-ink-800 rounded-none p-5 sm:p-10 lg:p-16 relative overflow-hidden border border-ink-700">
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-primary-700/10 rounded-full blur-3xl"></div>

            <div class="relative flex flex-col gap-8 lg:grid lg:grid-cols-12 lg:gap-8 lg:items-center">
                <div class="col-span-12 lg:col-span-8">
                    <span class="eyebrow !text-primary-400 mb-4 block">Готовы записаться?</span>
                    <h2 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(1.75rem,6vw,4.5rem)] text-ink-100">
                        Запись займёт две минуты.<br>
                        <span class="text-amber-gradient">Без звонков и ожиданий.</span>
                    </h2>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('booking') }}"
                           class="group inline-flex items-center justify-between gap-3 sm:gap-6 px-5 sm:px-7 py-4 sm:py-5 bg-primary-500 hover:bg-primary-400 text-white rounded-none font-bold transition-colors">
                            <span class="text-[13px] sm:text-[14px] uppercase tracking-wider">Записаться онлайн</span>
                            <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        @if($contact->phone)
                        <a href="{{ $contact->telHref() }}"
                           class="group inline-flex items-center justify-between gap-3 sm:gap-6 px-5 sm:px-7 py-4 sm:py-5 bg-black/[0.03] hover:bg-black/[0.06] border border-ink-700 text-ink-100 rounded-none font-bold transition-colors">
                            <span class="text-[13px] sm:text-[14px] uppercase tracking-wider">Позвонить</span>
                            <span class="font-mono text-[13px] sm:text-[14px] text-primary-400 whitespace-nowrap">{{ $contact->phone }}</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Индикатор прокрутки: ширина = доля прокрученной страницы (rAF, без джанка) --}}
<script>
(function () {
    var gauge = document.getElementById('scroll-gauge');
    if (! gauge) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var ticking = false;
    function update() {
        var doc = document.documentElement;
        var max = doc.scrollHeight - doc.clientHeight;
        var pct = max > 0 ? (doc.scrollTop / max) * 100 : 0;
        gauge.style.width = pct + '%';
        ticking = false;
    }
    window.addEventListener('scroll', function () {
        if (! ticking) { window.requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
})();
</script>

@endsection
