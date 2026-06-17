@extends('layouts.public')

@section('title', 'История обслуживания')
@section('description', 'Посмотрите детальную историю обращений в наш автосервис по email.')

@section('content')
<section class="relative overflow-hidden bg-ink-900 py-20 lg:py-28 min-h-[80vh]">

    <div class="glow w-[500px] h-[500px] -top-32 -right-32 bg-primary-600/25 animate-blob"></div>
    <div class="glow w-[400px] h-[400px] bottom-0 -left-32 bg-primary-500/15 animate-blob" style="animation-delay: -8s"></div>

    <div class="relative max-w-md mx-auto px-5">

        <div class="text-center mb-10 animate-fade-in">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-none bg-primary-500/15 text-primary-400 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="eyebrow block mb-3">Запрос истории</span>
            <h1 class="font-display font-extrabold tracking-tight leading-[1.05] text-[clamp(2rem,5vw,3rem)] text-ink-100">
                Ваша история<br>
                <span class="text-amber-gradient">обслуживания</span>
            </h1>
            <p class="text-ink-400 text-[15px] mt-5 leading-relaxed">
                Укажите email, на который оформляли заказ.<br>
                Мы пришлём код подтверждения.
            </p>
        </div>

        <div class="bg-ink-800 rounded-none p-7 lg:p-9 shadow-2xl border border-ink-700 animate-fade-in stagger-2">

            @if(session('status'))
                <div class="mb-5 px-4 py-3 bg-success-500/15 border border-success-500/30 text-success-500 text-[13px] rounded-none">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('lookup.send-code') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="eyebrow-muted block mb-3">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="ivanov@example.com"
                        class="w-full px-5 py-4 bg-ink-900 border border-ink-700 focus:border-primary-500 outline-none text-[15px] font-mono rounded-none transition-all text-ink-100"
                    >
                    @error('email')
                        <p class="text-primary-400 text-[13px] mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 py-4 bg-primary-500 hover:bg-primary-400 text-white text-[14px] font-bold uppercase tracking-wider rounded-none transition-colors">
                    Получить код
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>
        </div>

        <p class="text-center text-ink-400 text-[13px] mt-6 animate-fade-in stagger-3">
            Нет записи в нашей системе? <a href="{{ route('booking') }}" class="text-primary-400 font-semibold hover:text-primary-300 transition-colors">Записаться впервые →</a>
        </p>

    </div>
</section>
@endsection
