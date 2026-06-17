@extends('layouts.public')

@section('title', 'История обслуживания')

@section('content')
<section class="bg-ink-900 py-12 lg:py-16 min-h-screen relative overflow-hidden">

    <div class="glow w-[400px] h-[400px] top-0 -right-32 bg-primary-200/30 animate-blob"></div>

    <div class="relative max-w-[1100px] mx-auto px-5 lg:px-10">

        {{-- Шапка с данными клиента и сменой email --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 mb-10 animate-fade-in">
            <div class="flex items-center gap-4">
                <div class="shrink-0 w-14 h-14 rounded-none bg-primary-500 text-white flex items-center justify-center font-display font-bold text-2xl">
                    {{ mb_strtoupper(mb_substr($client->first_name ?: $client->last_name ?: 'К', 0, 1)) }}
                </div>
                <div>
                    <span class="eyebrow block mb-2">История обслуживания</span>
                    <h1 class="font-display font-extrabold text-[clamp(1.5rem,3vw,2.5rem)] tracking-tight text-ink-100 leading-none">
                        {{ $client->full_name ?: 'Клиент' }}
                    </h1>
                    <p class="text-ink-400 text-[13px] mt-2 font-mono">{{ $client->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('lookup.logout') }}">
                @csrf
                <button type="submit"
                        class="px-5 py-3 bg-ink-800 hover:bg-primary-500 hover:text-white border border-ink-700 hover:border-primary-500 text-ink-200 text-[12px] font-semibold uppercase tracking-wider transition-all">
                    Запросить по другому email
                </button>
            </form>
        </div>

        {{-- Сводка --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-5 mb-10 animate-fade-in stagger-2">
            <div class="bg-ink-800 rounded-none border border-ink-700 px-6 py-5 hover-lift">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-none bg-primary-500/10 text-primary-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                        </svg>
                    </div>
                    <span class="eyebrow-muted">Всего визитов</span>
                </div>
                <span class="font-display font-extrabold text-4xl text-ink-100 num-tabular tracking-tight">{{ $totalOrders }}</span>
            </div>

            <div class="bg-ink-800 rounded-none border border-ink-700 px-6 py-5 hover-lift">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-none bg-primary-500/10 text-primary-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9-1.5h-1.5a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5h12a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H17m-9.75 0v-9m0 9h9.75m-9.75 0V8.25h9.75v9.75m-9.75 0L9 16.5m0 0l1.5-1.5M9 16.5l1.5 1.5"/>
                        </svg>
                    </div>
                    <span class="eyebrow-muted">Автомобилей</span>
                </div>
                <span class="font-display font-extrabold text-4xl text-ink-100 num-tabular tracking-tight">{{ $client->cars->count() }}</span>
            </div>

            <div class="bg-ink-800 rounded-none border border-ink-700 px-6 py-5 hover-lift">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-none bg-primary-500/10 text-primary-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                    </div>
                    <span class="eyebrow-muted">Потрачено</span>
                </div>
                <span class="font-display font-extrabold text-4xl text-ink-100 num-tabular tracking-tight">{{ number_format($totalSpent, 0, ',', ' ') }} <span class="text-2xl text-ink-400">₽</span></span>
            </div>
        </div>

        {{-- Список заказов --}}
        @if($orders->isEmpty())
            <div class="bg-ink-800 rounded-none border border-ink-700 p-12 text-center animate-fade-in stagger-3">
                <div class="inline-flex w-16 h-16 rounded-none bg-ink-800 text-ink-400 items-center justify-center mb-5">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-ink-100 mb-2">Пока нет визитов</h3>
                <p class="text-ink-400 text-[14px] leading-relaxed mb-6 max-w-sm mx-auto">У вас ещё не было обращений в наш сервис. Запишитесь — будем рады видеть.</p>
                <a href="{{ route('booking') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-ink-900 hover:bg-primary-600 text-ink-100 hover:text-white text-[13px] font-semibold uppercase tracking-wider transition-colors">
                    Записаться
                    <span>→</span>
                </a>
            </div>
        @else
            <div class="space-y-5 animate-fade-in stagger-3">
                @foreach($orders as $order)
                    @php
                        $statuses = \App\Models\Order::statuses();
                        $statusLabel = $statuses[$order->status] ?? $order->status;
                        $statusClass = match($order->status) {
                            'closed', 'completed' => 'bg-success-500/15 text-success-500',
                            'in_progress'         => 'bg-primary-500/20 text-primary-400',
                            'cancelled'           => 'bg-red-500/15 text-red-400',
                            default               => 'bg-ink-700 text-ink-200',
                        };
                    @endphp

                    <div class="bg-ink-800 rounded-none border border-ink-700 overflow-hidden hover-lift">
                        {{-- Шапка заказа --}}
                        <div class="px-6 lg:px-8 py-5 border-b border-ink-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-4 flex-wrap">
                                <span class="font-mono font-bold text-ink-100 text-[15px]">№{{ $order->id }}</span>
                                <span class="text-ink-400 text-[13px]">{{ $order->created_at->format('d.m.Y') }}</span>
                                @if($order->car)
                                    <span class="text-ink-200 text-[13px] font-medium">
                                        {{ $order->car->brand?->name }} {{ $order->car->model?->name }}
                                        @if($order->car->vin)
                                            <span class="text-ink-400 font-mono ml-2">VIN {{ $order->car->vin }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <span class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        {{-- Услуги --}}
                        @if($order->services->isNotEmpty())
                            <div class="px-6 lg:px-8 py-5 border-b border-ink-700">
                                <span class="eyebrow-muted block mb-3">Услуги</span>
                                <div class="space-y-2.5">
                                    @foreach($order->services as $service)
                                        <div class="flex justify-between gap-4 text-[14px]">
                                            <span class="text-ink-200 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 bg-primary-500 rounded-full shrink-0"></span>
                                                {{ $service->name }}
                                                @if($service->pivot->quantity > 1)
                                                    <span class="text-ink-400">× {{ $service->pivot->quantity }}</span>
                                                @endif
                                            </span>
                                            <span class="text-ink-100 font-mono font-semibold whitespace-nowrap">{{ number_format($service->pivot->sum, 0, ',', ' ') }} ₽</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Запчасти --}}
                        @if($order->parts->isNotEmpty())
                            <div class="px-6 lg:px-8 py-5 border-b border-ink-700">
                                <span class="eyebrow-muted block mb-3">Запчасти</span>
                                <div class="space-y-2.5">
                                    @foreach($order->parts as $part)
                                        <div class="flex justify-between gap-4 text-[14px]">
                                            <span class="text-ink-200 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 bg-primary-400 rounded-full shrink-0"></span>
                                                {{ $part->name }}
                                                @if($part->pivot->quantity > 1)
                                                    <span class="text-ink-400">× {{ $part->pivot->quantity }}</span>
                                                @endif
                                            </span>
                                            <span class="text-ink-100 font-mono font-semibold whitespace-nowrap">{{ number_format($part->pivot->sum, 0, ',', ' ') }} ₽</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Итого --}}
                        <div class="px-6 lg:px-8 py-5 bg-ink-900 grid grid-cols-2 gap-4 text-[13px]">
                            <div>
                                <span class="eyebrow-muted block mb-1">Сумма</span>
                                <span class="font-mono font-bold text-ink-100 text-[16px]">{{ number_format($order->total_amount, 0, ',', ' ') }} ₽</span>
                            </div>
                            <div>
                                <span class="eyebrow-muted block mb-1">Оплачено</span>
                                <span class="font-mono font-bold text-success-500 text-[16px]">{{ number_format($order->paid_amount, 0, ',', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</section>
@endsection
