<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mobile 1') — Ремонт и обслуживание автомобилей</title>
    <meta name="description" content="@yield('description', 'Современный автосервис полного цикла. Запись онлайн, гарантия на работы, опытные мастера.')">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter+Tight:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Яндекс Карты JS API v2 --}}
    <script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('services.yandex_maps.api_key') }}&lang=ru_RU" async></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="public-scope bg-ink-900 text-ink-100 antialiased font-sans">

{{-- ═════════════════════════ ЛОАДЕР («прогрев двигателя») ═════════════════════════ --}}
<div id="app-loader">
    <img src="{{ asset('storage/logo/logo.svg') }}" alt="Mobile 1" class="loader-logo">
    <div class="loader-brand">Mobile 1</div>
    <div class="loader-track"><div class="loader-fill" id="loader-fill"></div></div>
    <div class="loader-counter"><span id="loader-counter">0</span>%</div>
</div>
<script>
(function () {
    var loader  = document.getElementById('app-loader');
    var fill    = document.getElementById('loader-fill');
    var counter = document.getElementById('loader-counter');
    if (!loader) return;

    document.documentElement.style.overflow = 'hidden';

    var value = 0;
    var tick = setInterval(function () {
        // Неравномерный «прогрев»: быстро в начале, замедление к концу
        value += Math.max(1, Math.round((100 - value) * 0.08));
        if (value >= 100) { value = 100; clearInterval(tick); }
        if (fill) fill.style.right = (100 - value) + '%';
        if (counter) counter.textContent = value;
    }, 70);

    function hide() {
        if (fill) fill.style.right = '0%';
        if (counter) counter.textContent = '100';
        clearInterval(tick);
        loader.classList.add('is-hidden');
        document.documentElement.style.overflow = '';
        setTimeout(function () { if (loader && loader.parentNode) loader.parentNode.removeChild(loader); }, 800);
    }

    var done = false;
    function finish() { if (done) return; done = true; setTimeout(hide, 350); }

    window.addEventListener('load', finish);
    // Подстраховка: скрыть максимум через 3.5с, даже если что-то не догрузилось
    setTimeout(finish, 3500);
    // bfcache: при возврате/переходе страница может восстановиться из кэша уже
    // «загруженной» — событие load не повторится. Снимаем лоадер и тут, иначе
    // он залипнет поверх страницы и скроет шапку.
    window.addEventListener('pageshow', function (e) { if (e.persisted) finish(); });
})();
</script>

{{-- ═════════════════════════════════════ ХЕДЕР ═════════════════════════════════════ --}}
@php
    $navLinks = [
        ['#services',     'Услуги'],
        ['#how-it-works', 'Как это работает'],
        ['#advantages',   'О нас'],
        ['#branches',     'Адреса'],
    ];
    // $contact (контакты сайта) приходит из View Composer (AppServiceProvider).
@endphp
<header
    x-data="{ scrolled: false, mobileOpen: false }"
    @scroll.window="scrolled = window.scrollY > 8"
    @keydown.escape.window="mobileOpen = false"
    @resize.window="if (window.innerWidth >= 1024) mobileOpen = false"
    x-effect="document.body.style.overflow = mobileOpen ? 'hidden' : ''"
    class="fixed top-0 inset-x-0 z-50 transition-shadow duration-300"
    :class="(scrolled || mobileOpen) ? 'shadow-[0_8px_30px_-16px_rgba(13,30,55,0.35)]' : ''">

    {{-- Полоса шапки — сплошной белый фон (без backdrop-blur: на мобильных
         фиксированный элемент с backdrop-filter после навигации/скролла
         перестаёт перерисовываться, и содержимое шапки, включая бургер,
         пропадает; к тому же блюр-«стекло» запрещён в DESIGN.md). --}}
    <div class="relative z-20 border-b transition-colors duration-300"
         :class="(scrolled || mobileOpen) ? 'bg-white border-ink-700' : 'bg-white border-transparent'">
        <div class="max-w-[1400px] mx-auto px-5 lg:px-10">
            <div class="flex items-center justify-between transition-[height] duration-300 ease-out"
                 :class="scrolled ? 'h-[60px] lg:h-[68px]' : 'h-[68px] lg:h-[80px]'">

                {{-- Логотип --}}
                <a href="{{ url('/') }}" class="flex items-center group shrink-0" aria-label="На главную">
                    <img src="{{ asset('storage/logo/logo.svg') }}" alt="Mobile 1"
                         class="w-auto block transition-all duration-300 group-hover:scale-105"
                         :class="scrolled ? 'h-6 lg:h-7' : 'h-7 lg:h-8'">
                </a>

                {{-- Десктоп-меню: подчёркивание-индикатор --}}
                <nav class="hidden lg:flex items-center gap-1">
                    @foreach($navLinks as [$href, $label])
                    <a href="{{ url('/') }}{{ $href }}"
                       class="relative px-3.5 py-2 text-[13.5px] font-semibold text-ink-300 hover:text-primary-600 transition-colors duration-200
                              after:absolute after:left-3.5 after:right-3.5 after:-bottom-0.5 after:h-[2px] after:rounded-full after:bg-primary-500
                              after:origin-left after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:ease-out">{{ $label }}</a>
                    @endforeach
                </nav>

                {{-- Правая часть --}}
                <div class="flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('lookup.form') }}"
                       class="relative hidden md:inline-flex items-center gap-2 px-3.5 py-2 text-[13.5px] font-semibold text-ink-300 hover:text-primary-600 transition-colors duration-200
                              after:absolute after:left-3.5 after:right-3.5 after:-bottom-0.5 after:h-[2px] after:rounded-full after:bg-primary-500
                              after:origin-left after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:duration-300 after:ease-out">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        История
                    </a>

                    {{-- Бургер --}}
                    <button type="button" @click="mobileOpen = !mobileOpen"
                            :aria-expanded="mobileOpen" aria-controls="mobile-nav" aria-label="Меню"
                            class="lg:hidden flex items-center justify-center w-11 h-11 -mr-1.5 rounded-md text-ink-100 hover:text-primary-600 hover:bg-ink-900 transition-colors">
                        <span class="burger" :class="{ 'is-open': mobileOpen }">
                            <span></span><span></span><span></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Подложка (затемнение страницы под меню) --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="lg:hidden fixed inset-0 z-0 bg-ink-950/60"></div>

    {{-- Мобильное меню: сплошной лист, выезжает из-под шапки --}}
    <div id="mobile-nav" x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-[400ms]"
         x-transition:enter-start="opacity-0 -translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-3"
         :class="scrolled ? 'top-[60px]' : 'top-[68px]'"
         class="lg:hidden fixed inset-x-0 z-10 bg-white border-b border-ink-700 shadow-[0_28px_50px_-28px_rgba(13,30,55,0.45)] max-h-[calc(100dvh_-_56px)] overflow-y-auto overscroll-contain">

        <div class="px-5 py-3">
            <nav class="flex flex-col">
                @foreach($navLinks as $i => [$href, $label])
                <a href="{{ url('/') }}{{ $href }}" @click="mobileOpen = false"
                   class="nav-m-item group flex items-center justify-between gap-4 py-4 border-b border-ink-700 text-[17px] font-semibold text-ink-100 hover:text-primary-600 transition-colors"
                   style="--i: {{ $i }}">
                    <span>{{ $label }}</span>
                    <svg class="w-4 h-4 text-ink-400 group-hover:text-primary-500 group-hover:translate-x-0.5 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach

                <a href="{{ route('lookup.form') }}" @click="mobileOpen = false"
                   class="nav-m-item group flex items-center justify-between gap-4 py-4 text-[17px] font-semibold text-ink-100 hover:text-primary-600 transition-colors"
                   style="--i: {{ count($navLinks) }}">
                    <span class="inline-flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-ink-400 group-hover:text-primary-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        История обслуживания
                    </span>
                    <svg class="w-4 h-4 text-ink-400 group-hover:text-primary-500 group-hover:translate-x-0.5 transition-all duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </nav>

            {{-- Запись + телефон --}}
            <div class="nav-m-item pt-5 pb-2" style="--i: {{ count($navLinks) + 1 }}">
                <a href="{{ route('booking') }}" @click="mobileOpen = false" class="btn-primary w-full justify-center group">
                    Записаться онлайн
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                @if($contact->phone)
                <a href="{{ $contact->telHref() }}"
                   class="mt-3 flex items-center justify-center gap-2 py-2.5 text-[14px] font-semibold text-ink-300 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                    </svg>
                    {{ $contact->phone }}
                </a>
                @endif
            </div>
        </div>
    </div>
</header>

<main class="pt-[68px] lg:pt-[80px]">
    @yield('content')
</main>

{{-- ═════════════════════════════════════ ФУТЕР ═════════════════════════════════════ --}}
<footer class="bg-ink-900 text-ink-100 relative overflow-hidden">

    {{-- Декоративный blob --}}
    <div class="glow w-[500px] h-[500px] -top-40 -right-40 bg-primary-600/20 animate-blob"></div>

    <div class="max-w-[1400px] mx-auto px-5 lg:px-10 pt-24 pb-12 relative">

        {{-- Верхняя часть: большой CTA-блок --}}
        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-12 lg:gap-12 mb-20">
            <div class="col-span-12 lg:col-span-7">
                <span class="eyebrow !text-primary-400">Готовы помочь</span>
                <h2 class="font-display font-extrabold tracking-tight leading-[1] text-[clamp(2rem,6vw,4.5rem)] mt-5 text-balance">
                    Запишитесь на<br>
                    <span class="text-amber-gradient">обслуживание</span>
                </h2>
            </div>
            <div class="col-span-12 lg:col-span-5 lg:pt-10 flex items-end">
                <p class="text-ink-300 text-[16px] leading-relaxed max-w-md">
                    Два клика — и заявка с выбранным временем уходит в сервис.
                </p>
            </div>
        </div>

        {{-- Колонки футера --}}
        <div class="flex flex-col gap-8 lg:grid lg:grid-cols-12 lg:gap-12 pb-12 border-b border-ink-700">

            {{-- Лого + описание --}}
            <div class="col-span-12 lg:col-span-5">
                <div class="mb-6">
                    <a href="{{ url('/') }}" class="inline-flex">
                        <img src="{{ asset('storage/logo/logo.svg') }}" alt="Mobile 1" class="h-9 w-auto block">
                    </a>
                </div>
                <p class="text-ink-300 text-[14px] leading-relaxed max-w-md mb-8">
                    Профессиональный ремонт и техническое обслуживание автомобилей всех марок. Прозрачные цены, гарантия на все работы.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('booking') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-primary-500 hover:bg-primary-400 text-white font-semibold text-[13px] transition-colors">
                        Записаться онлайн
                        <span>→</span>
                    </a>
                    <a href="{{ route('lookup.form') }}" class="inline-flex items-center gap-2 px-5 py-3 border border-ink-700 hover:border-ink-500 text-ink-200 font-semibold text-[13px] transition-colors">
                        Моя история
                    </a>
                </div>
            </div>

            {{-- Сервис --}}
            <div class="col-span-6 lg:col-span-2">
                <span class="eyebrow !text-primary-400 mb-5 block">Сервис</span>
                <ul class="space-y-3">
                    <li><a href="{{ url('/') }}#services" class="text-ink-200 hover:text-primary-400 text-[14px] transition-colors">Услуги</a></li>
                    <li><a href="{{ url('/') }}#how-it-works" class="text-ink-200 hover:text-primary-400 text-[14px] transition-colors">Процесс</a></li>
                    <li><a href="{{ url('/') }}#advantages" class="text-ink-200 hover:text-primary-400 text-[14px] transition-colors">О нас</a></li>
                </ul>
            </div>

            {{-- Филиалы --}}
            <div class="col-span-6 lg:col-span-2">
                <span class="eyebrow !text-primary-400 mb-5 block">Адреса</span>
                <ul class="space-y-3">
                    @foreach(\App\Models\Branch::where('active', true)->take(4)->get() as $branch)
                    <li>
                        <a href="{{ url('/') }}#branches" class="block text-ink-200 hover:text-primary-400 text-[14px] leading-tight transition-colors">
                            {{ $branch->name }}
                            @if($branch->city)<span class="block text-ink-500 text-[11px] mt-1">{{ $branch->city }}</span>@endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Контакты --}}
            <div class="col-span-12 lg:col-span-3">
                <span class="eyebrow !text-primary-400 mb-5 block">Контакты</span>
                <ul class="space-y-4">
                    @if($contact->phone)
                    <li>
                        <span class="text-ink-500 text-[11px] uppercase tracking-wider block mb-1">Телефон</span>
                        <a href="{{ $contact->telHref() }}" class="text-ink-100 font-mono text-[16px] hover:text-primary-400 transition-colors">{{ $contact->phone }}</a>
                    </li>
                    @endif
                    @if($contact->email)
                    <li>
                        <span class="text-ink-500 text-[11px] uppercase tracking-wider block mb-1">Email</span>
                        <a href="mailto:{{ $contact->email }}" class="text-ink-100 text-[14px] hover:text-primary-400 transition-colors">{{ $contact->email }}</a>
                    </li>
                    @endif
                    @if($contact->working_hours)
                    <li>
                        <span class="text-ink-500 text-[11px] uppercase tracking-wider block mb-1">Часы работы</span>
                        <span class="text-ink-100 text-[14px]">{{ $contact->working_hours }}</span>
                    </li>
                    @endif
                    @if($contact->whatsapp || $contact->telegram || $contact->vk)
                    <li>
                        <span class="text-ink-500 text-[11px] uppercase tracking-wider block mb-2">Мы в сети</span>
                        <div class="flex flex-wrap gap-3 text-[13px]">
                            @if($contact->whatsapp)<a href="{{ $contact->whatsapp }}" target="_blank" rel="noopener" class="text-ink-200 hover:text-primary-400 transition-colors">WhatsApp</a>@endif
                            @if($contact->telegram)<a href="{{ $contact->telegram }}" target="_blank" rel="noopener" class="text-ink-200 hover:text-primary-400 transition-colors">Telegram</a>@endif
                            @if($contact->vk)<a href="{{ $contact->vk }}" target="_blank" rel="noopener" class="text-ink-200 hover:text-primary-400 transition-colors">ВКонтакте</a>@endif
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Низ футера --}}
        <div class="pt-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-[12px] text-ink-500">
            <div>© {{ date('Y') }} Mobile 1. Все права защищены.</div>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-primary-400 transition-colors">Политика конфиденциальности</a>
                <a href="#" class="hover:text-primary-400 transition-colors">Договор-оферта</a>
            </div>
        </div>
    </div>
</footer>

@livewireScripts
</body>
</html>
