@extends('layouts.public')

@section('title', 'Подтверждение входа')

@section('content')
<section class="relative overflow-hidden bg-ink-900 py-20 lg:py-28 min-h-[80vh]">

    <div class="glow w-[500px] h-[500px] -top-32 -left-32 bg-primary-600/25 animate-blob"></div>
    <div class="glow w-[400px] h-[400px] bottom-0 -right-32 bg-primary-500/15 animate-blob" style="animation-delay: -8s"></div>

    <div class="relative max-w-md mx-auto px-5">

        <div class="text-center mb-10 animate-fade-in">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-none bg-primary-500/15 text-primary-400 mb-6">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25"/>
                </svg>
            </div>

            <span class="eyebrow block mb-3">Шаг 2 из 2</span>
            <h1 class="font-display font-extrabold tracking-tight leading-[1.05] text-[clamp(2rem,5vw,3rem)] text-ink-100">
                Введите<br>
                <span class="text-amber-gradient">код подтверждения</span>
            </h1>
            <p class="text-ink-400 text-[15px] mt-5 leading-relaxed">
                Мы отправили 6-значный код на<br>
                <span class="font-mono font-semibold text-ink-100">{{ $email }}</span>
            </p>
        </div>

        <div class="bg-ink-800 rounded-none p-7 lg:p-9 shadow-2xl border border-ink-700 animate-fade-in stagger-2">

            @if(session('status'))
                <div class="mb-5 px-4 py-3 bg-success-500/15 border border-success-500/30 text-success-500 text-[13px] rounded-none">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('lookup.verify') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="code" class="eyebrow-muted block mb-3">Код из письма</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        required
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="w-full px-5 py-5 bg-ink-900 border border-ink-700 focus:border-primary-500 outline-none text-center text-3xl font-mono tracking-[0.6em] text-ink-100 rounded-none transition-all"
                    >
                    @error('code')
                        <p class="text-primary-400 text-[13px] mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 py-4 bg-primary-500 hover:bg-primary-400 text-white text-[14px] font-bold uppercase tracking-wider rounded-none transition-colors">
                    Войти и посмотреть историю
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('lookup.form') }}" class="text-ink-400 hover:text-ink-100 text-[13px] font-medium transition-colors">
                    ← Указать другой email
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
