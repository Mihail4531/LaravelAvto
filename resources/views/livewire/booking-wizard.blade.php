<div class="min-h-screen bg-ink-900">

    {{-- Хедер визарда --}}
    <div class="border-b border-ink-700">
        <div class="max-w-[960px] mx-auto px-5 lg:px-10 py-10 lg:py-14">
            <div class="flex items-center gap-3 mb-10 lg:mb-14">
                <span class="eyebrow-muted">— Онлайн-запись</span>
                <span class="flex-1 h-px bg-ink-700"></span>
                <a href="{{ url('/') }}" class="eyebrow-muted hover:text-ink-100 transition-colors">← Назад на сайт</a>
            </div>

            <h1 class="font-display font-extrabold text-balance leading-[1.05] tracking-tight text-[clamp(2rem,5vw,3.5rem)] text-ink-100">
                @if($submitted)
                    Запись<br><span class="text-ink-400 italic font-medium">подтверждена.</span>
                @else
                    Запись<br>на обслуживание
                @endif
            </h1>
            @unless($submitted)
            <p class="text-ink-400 text-[15px] leading-relaxed mt-4 max-w-md">Заполните форму — это займёт не более двух минут.</p>
            @endunless
        </div>
    </div>

    <div class="max-w-[960px] mx-auto px-5 lg:px-10 py-10 lg:py-16">

        @if($submitted)
        {{-- ═════ УСПЕХ ═════ --}}
        <div class="animate-fade-in">
            <div class="confirm-stamp relative w-20 h-20 mb-8 bg-primary-500/15 border-2 border-primary-500/60 flex items-center justify-center" aria-hidden="true">
                <svg class="w-11 h-11 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="border border-primary-600/40 bg-primary-500/[0.06] p-6 lg:p-8 mb-10">
                <div class="font-mono text-[13px] text-primary-500 mb-3">№ {{ str_pad($appointmentId, 5, '0', STR_PAD_LEFT) }}</div>
                <p class="text-ink-200 text-lg leading-relaxed max-w-xl">
                    Ваша заявка получена. Менеджер свяжется с вами по номеру
                    <span class="font-mono text-ink-100">{{ $clientPhone }}</span>
                    в течение часа для подтверждения.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 border-t border-l border-ink-700 mb-10">
                @if($this->selectedSlot)
                <div class="border-b border-r border-ink-700 p-6">
                    <div class="eyebrow-muted mb-2">Дата и время</div>
                    <div class="font-display font-bold text-lg text-ink-100">{{ $this->selectedSlot->starts_at->isoFormat('D MMMM') }}</div>
                    <div class="font-mono text-ink-400">{{ $this->selectedSlot->starts_at->format('H:i') }}</div>
                </div>
                @endif
                @if($this->selectedBranch)
                <div class="border-b border-r border-ink-700 p-6">
                    <div class="eyebrow-muted mb-2">Филиал</div>
                    <div class="font-display font-bold text-lg text-ink-100">{{ $this->selectedBranch->name }}</div>
                    <div class="text-ink-400 text-[13px] mt-1 leading-snug">{{ $this->selectedBranch->city ? $this->selectedBranch->city . ', ' : '' }}{{ $this->selectedBranch->address }}</div>
                </div>
                @endif
                <div class="border-b border-r border-ink-700 p-6">
                    <div class="eyebrow-muted mb-2">Услуг выбрано</div>
                    <div class="font-display font-bold text-lg text-ink-100 num-tabular">{{ count($serviceIds) }}</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ url('/') }}" class="group inline-flex items-center justify-between gap-8 px-7 py-5 border border-ink-700 text-ink-100 hover:border-ink-500 hover:bg-ink-800 transition-colors duration-300">
                    <span class="text-[12px] font-bold uppercase tracking-widest">На главную</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
                </a>
                <a href="{{ route('booking') }}" class="group inline-flex items-center justify-between gap-8 px-7 py-5 bg-ink-800 border border-ink-700 hover:bg-primary-700 hover:border-primary-700 text-ink-100 hover:text-white transition-colors duration-300">
                    <span class="text-[12px] font-bold uppercase tracking-widest">Ещё одна запись</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
                </a>
            </div>
        </div>
        @else

        {{-- ═════ ПРОГРЕСС — сегментная шкала ═════ --}}
        <div class="mb-10 lg:mb-14">
            <div class="flex items-center justify-between mb-4">
                @foreach([
                    [1, 'Услуги'],
                    [2, 'Время'],
                    [3, 'Контакты'],
                    [4, 'Подтверждение'],
                ] as [$num, $label])
                <div class="flex items-center gap-2.5 {{ $step === $num ? 'text-ink-100' : ($step > $num ? 'text-ink-200' : 'text-ink-500') }}">
                    <span class="flex items-center justify-center w-6 h-6 shrink-0 border font-mono text-[11px] num-tabular transition-colors duration-300
                        {{ $step > $num ? 'bg-primary-500 border-primary-500 text-white'
                            : ($step === $num ? 'border-primary-500 text-primary-400' : 'border-ink-700 text-ink-500') }}">
                        {{ $step > $num ? '✓' : $num }}
                    </span>
                    <span class="hidden sm:inline text-[13px] font-medium">{{ $label }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex gap-1.5">
                @for($i = 1; $i <= 4; $i++)
                <div class="flex-1 h-1 transition-colors duration-500 ease-out {{ $i <= $step ? 'bg-primary-500' : 'bg-ink-700' }}"></div>
                @endfor
            </div>
        </div>

        {{-- ═════ ШАГИ ═════ --}}

        {{-- ── ШАГ 1: УСЛУГИ (клиентская фильтрация, без лагов) ── --}}
        @if($step === 1)
        @php
            $catalogFlat = $this->serviceCatalog->flatMap(fn ($c) =>
                $c->services->map(fn ($s) => [
                    'id'       => $s->id,
                    'name'     => $s->name,
                    'price'    => (float) $s->price,
                    'duration' => $s->duration_minutes,
                ])
            )->values();
        @endphp
        <div class="{{ $stepDirection === 'back' ? 'wizard-step-back' : 'wizard-step-fwd' }}" wire:key="step-1"
             x-data="{
                activeCat: 'all',
                selected: @js(array_map('intval', $serviceIds)),
                services: @js($catalogFlat),
                toggle(id) {
                    const i = this.selected.indexOf(id);
                    if (i === -1) this.selected.push(id); else this.selected.splice(i, 1);
                    $wire.serviceIds = [...this.selected];
                },
                has(id) { return this.selected.includes(id); },
                get chosen() { return this.services.filter(s => this.selected.includes(s.id)); },
                fmt(n) { return new Intl.NumberFormat('ru-RU').format(n); },
             }">

            {{-- Заголовок --}}
            <div class="mb-8">
                <h2 class="font-display font-extrabold tracking-tight leading-[1.02] text-[clamp(1.9rem,4.5vw,3.25rem)] text-ink-100 text-balance">
                    Выберите <span class="text-primary-400">услуги</span>
                </h2>
                <p class="text-ink-300 text-[15px] mt-4 max-w-xl leading-relaxed">
                    Отметьте нужные работы — можно из разных категорий. Выбор сохраняется при переключении.
                </p>
            </div>

            @error('serviceIds')
            <div class="mb-6 px-4 py-3 bg-primary-500/10 border border-primary-500/40 text-primary-300 text-[13px]">{{ $message }}</div>
            @enderror

            {{-- Сводка выбранного (мгновенно, на клиенте) --}}
            <div x-show="selected.length" x-cloak x-transition.opacity
                 class="mb-8 card-steel p-5 lg:p-6">
                <div class="flex items-baseline justify-between gap-3 mb-4">
                    <div class="eyebrow">Уже выбрано</div>
                    <div class="font-mono text-[12px] text-ink-400 num-tabular"><span x-text="selected.length"></span> шт.</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="s in chosen" :key="s.id">
                        <button type="button" @click="toggle(s.id)"
                                class="pop-in group inline-flex items-center gap-2 pl-3 pr-2 py-1.5 bg-primary-500 text-white text-[13px] font-semibold hover:bg-primary-400 transition-all duration-200 active:scale-95">
                            <span class="leading-none" x-text="s.name"></span>
                            <span class="w-4 h-4 inline-flex items-center justify-center bg-ink-900/20">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Табы категорий (клиентские) --}}
            <div class="flex flex-wrap gap-2 mb-8">
                <button type="button" @click="activeCat = 'all'"
                        :class="activeCat === 'all' ? 'bg-primary-500 text-white border-primary-500' : 'text-ink-300 border-ink-700 hover:border-ink-500 hover:text-ink-100'"
                        class="px-5 py-2.5 text-[13px] font-semibold border transition-all duration-200 active:scale-95">
                    Все услуги
                </button>
                @foreach($this->serviceCatalog as $cat)
                <button type="button" @click="activeCat = {{ $cat->id }}"
                        :class="activeCat === {{ $cat->id }} ? 'bg-primary-500 text-white border-primary-500' : 'text-ink-300 border-ink-700 hover:border-ink-500 hover:text-ink-100'"
                        class="px-5 py-2.5 text-[13px] font-semibold border transition-all duration-200 active:scale-95">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>

            {{-- Сетка карточек услуг --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->serviceCatalog as $cat)
                    @foreach($cat->services as $service)
                    <div x-show="activeCat === 'all' || activeCat === {{ $cat->id }}"
                         @click="toggle({{ $service->id }})"
                         :class="has({{ $service->id }}) ? 'border-primary-500 bg-primary-500/[0.06]' : 'border-ink-700 hover:border-ink-500'"
                         class="relative cursor-pointer bg-ink-800 border p-5 transition-all duration-200 group active:scale-[0.98]">

                        {{-- Чекбокс-индикатор --}}
                        <div class="absolute top-4 right-4 w-6 h-6 border flex items-center justify-center transition-all duration-200"
                             :class="has({{ $service->id }}) ? 'bg-primary-500 border-primary-500 scale-110' : 'border-ink-600 group-hover:border-ink-400'">
                            <svg x-show="has({{ $service->id }})" class="pop-in w-4 h-4 text-ink-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12.75l4 4 9-9"/></svg>
                        </div>

                        <div class="font-display font-semibold text-[16px] text-ink-100 leading-snug mb-2 pr-8">{{ $service->name }}</div>
                        @if($service->description)
                        <p class="text-[13px] text-ink-400 leading-relaxed line-clamp-2 mb-4">{{ $service->description }}</p>
                        @endif

                        <div class="flex items-center justify-between gap-3 pt-3 border-t border-ink-700/60">
                            @if($service->price)
                            <span class="font-mono text-[14px] text-ink-100">
                                <span class="text-ink-400 text-[11px]">от</span> {{ number_format($service->price, 0, '.', ' ') }} ₽
                            </span>
                            @else
                            <span class="text-success-500 text-[13px] font-semibold">Бесплатно</span>
                            @endif
                            @if($service->duration_minutes)
                            <span class="font-mono text-[12px] text-ink-500">~{{ $service->duration_minutes }} мин</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>

            {{-- Счётчик выбранного --}}
            <div x-show="selected.length" x-cloak class="mt-8 flex items-baseline gap-3">
                <span class="font-mono text-[13px] text-ink-400 num-tabular">Выбрано</span>
                <span class="font-display font-extrabold text-2xl text-primary-400 num-tabular" x-text="selected.length"></span>
                <span class="text-ink-400 text-[14px]">услуг(и)</span>
            </div>
        </div>
        @endif

        {{-- ── ШАГ 2: ВРЕМЯ ── --}}
        @if($step === 2)
        <div class="{{ $stepDirection === 'back' ? 'wizard-step-back' : 'wizard-step-fwd' }}" wire:key="step-2">

            <h2 class="font-display font-extrabold text-[clamp(1.6rem,4vw,2.5rem)] tracking-tight text-ink-100 mb-2 text-balance">{{ $this->hasBranchChoice ? 'Когда и где' : 'Когда вам удобно' }}</h2>
            <p class="text-ink-400 text-[14px] mb-10">{{ $this->hasBranchChoice ? 'Выберите филиал и удобное время визита.' : 'Выберите удобное время визита.' }}</p>

            @error('branchId')<div class="mb-4 px-4 py-3 bg-primary-500/10 border border-primary-500/40 text-primary-300 text-[13px]">{{ $message }}</div>@enderror
            @error('slotId')<div class="mb-4 px-4 py-3 bg-primary-500/10 border border-primary-500/40 text-primary-300 text-[13px]">{{ $message }}</div>@enderror

            {{-- Филиалы --}}
            @if($this->hasBranchChoice)
            <div class="mb-10">
                <div class="eyebrow-muted mb-4">Филиал</div>
                <ul class="border-t border-ink-700">
                    @foreach($this->branches as $i => $branch)
                    <li>
                        <button wire:click="selectBranch({{ $branch->id }})"
                                class="group w-full text-left py-5 px-1 flex items-start gap-5 border-b border-ink-700 hover:bg-ink-900 transition-colors {{ $branchId === $branch->id ? 'bg-ink-900' : '' }}">
                            <div class="font-mono text-[12px] text-ink-400 num-tabular pt-1 shrink-0">
                                {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-display font-bold text-[16px] text-ink-100 mb-1">{{ $branch->name }}</div>
                                @if($branch->address)
                                <div class="text-[13px] text-ink-400 leading-snug">{{ $branch->city ? $branch->city . ', ' : '' }}{{ $branch->address }}</div>
                                @endif
                                @if($branch->work_schedule)
                                <div class="font-mono text-[11px] text-ink-400 mt-1.5">{{ $branch->work_schedule }}</div>
                                @endif
                            </div>
                            <div class="w-5 h-5 border {{ $branchId === $branch->id ? 'bg-ink-900 border-ink-900' : 'border-ink-600 group-hover:border-ink-500' }} shrink-0 flex items-center justify-center mt-1 transition-colors">
                                @if($branchId === $branch->id)
                                <svg class="w-3 h-3 text-ink-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12.75l4 4 9-9"/></svg>
                                @endif
                            </div>
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>
            @elseif($this->selectedBranch)
            {{-- Один филиал — показываем как адрес визита, без выбора --}}
            <div class="mb-10">
                <div class="eyebrow-muted mb-4">Адрес визита</div>
                <div class="border-t border-b border-ink-700 py-5 px-1 flex items-start gap-5">
                    <div class="flex-1 min-w-0">
                        <div class="font-display font-bold text-[16px] text-ink-100 mb-1">{{ $this->selectedBranch->name }}</div>
                        @if($this->selectedBranch->address)
                        <div class="text-[13px] text-ink-400 leading-snug">{{ $this->selectedBranch->city ? $this->selectedBranch->city . ', ' : '' }}{{ $this->selectedBranch->address }}</div>
                        @endif
                        @if($this->selectedBranch->work_schedule)
                        <div class="font-mono text-[11px] text-ink-400 mt-1.5">{{ $this->selectedBranch->work_schedule }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="mb-10 py-10 text-center text-ink-400 text-[14px] border-t border-b border-ink-700">Филиалы не настроены</div>
            @endif

            {{-- Слоты --}}
            @if($branchId)
            <div class="animate-fade-in">
                <div class="eyebrow-muted mb-4">Дата и время</div>

                @if($this->timeSlots->isEmpty())
                <div class="border border-ink-700 p-8 lg:p-10">
                    <div class="font-mono text-[12px] text-primary-400 mb-3">— Нет окон</div>
                    <h3 class="font-display font-bold text-xl text-ink-100 mb-2">Свободного времени нет</h3>
                    <p class="text-ink-400 text-[14px] leading-relaxed max-w-md mb-6">
                        Сейчас нет свободных окон. Позвоните — поможем подобрать удобное время{{ $this->hasBranchChoice ? ' или предложим другой филиал' : '' }}.
                    </p>
                    <a href="tel:+78000000000" class="inline-flex items-center gap-3 px-5 py-3 border border-ink-700 hover:border-ink-500 hover:bg-ink-800 transition-colors text-[13px] font-semibold">
                        <span class="font-mono">+7 800 000 00 00</span>
                    </a>
                </div>
                @else
                <div x-data="{ activeDate: '{{ $this->timeSlots->keys()->first() }}' }">
                    {{-- Даты --}}
                    <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
                        @foreach($this->timeSlots as $date => $slots)
                        @php $d = \Carbon\Carbon::parse($date); @endphp
                        <button @click="activeDate = '{{ $date }}'"
                                :class="activeDate === '{{ $date }}'
                                    ? 'border-primary-500 bg-primary-500/[0.08] text-ink-100'
                                    : 'border-ink-700 bg-ink-800 text-ink-300 hover:border-ink-500 hover:text-ink-100'"
                                class="shrink-0 border px-5 py-3 text-center min-w-[88px] transition-colors duration-200">
                            <div class="text-[11px] font-semibold uppercase tracking-wider opacity-80">{{ $d->translatedFormat('D') }}</div>
                            <div class="font-display font-extrabold text-2xl mt-1 num-tabular leading-none">{{ $d->format('d') }}</div>
                            <div class="font-mono text-[11px] mt-1 opacity-60">{{ $d->translatedFormat('M') }}</div>
                            <div class="font-mono text-[10px] mt-2 num-tabular"
                                 :class="activeDate === '{{ $date }}' ? 'text-primary-400' : 'text-ink-500'">{{ $slots->count() }} своб.</div>
                        </button>
                        @endforeach
                    </div>

                    {{-- Слоты-чипы --}}
                    @foreach($this->timeSlots as $date => $slots)
                    <div x-show="activeDate === '{{ $date }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-5 grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-6 gap-2">
                        @foreach($slots as $slot)
                        <button wire:click="selectSlot({{ $slot->id }})"
                                class="py-3 px-2 text-center text-[14px] font-mono num-tabular border transition-all duration-150 active:scale-95
                                    {{ $slotId === $slot->id
                                        ? 'bg-primary-500 text-white border-primary-500 shadow-[0_0_18px_-3px_rgba(26,109,255,.65)]'
                                        : 'bg-ink-800 text-ink-100 border-ink-700 hover:border-primary-500 hover:text-ink-100' }}">
                            {{ $slot->starts_at->format('H:i') }}
                        </button>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- ── ШАГ 3: КОНТАКТЫ ── --}}
        @if($step === 3)
        <div class="{{ $stepDirection === 'back' ? 'wizard-step-back' : 'wizard-step-fwd' }}" wire:key="step-3">

            <h2 class="font-display font-extrabold text-[clamp(1.6rem,4vw,2.5rem)] tracking-tight text-ink-100 mb-2 text-balance">Как с вами связаться</h2>
            <p class="text-ink-400 text-[14px] mb-10">Менеджер позвонит для подтверждения. Email и данные авто — по желанию.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Фамилия --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Фамилия*</label>
                    <input wire:model.blur="clientLastName" type="text" placeholder="Иванов"
                           class="w-full bg-transparent border-0 border-b border-ink-700 focus:border-primary-500 focus:outline-none text-[16px] text-ink-100 py-2 transition-colors @error('clientLastName') border-primary-600 @enderror">
                    @error('clientLastName')<p class="mt-2 text-[12px] text-primary-300">{{ $message }}</p>@enderror
                </div>

                {{-- Имя --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Имя*</label>
                    <input wire:model.blur="clientFirstName" type="text" placeholder="Иван"
                           class="w-full bg-transparent border-0 border-b border-ink-700 focus:border-primary-500 focus:outline-none text-[16px] text-ink-100 py-2 transition-colors @error('clientFirstName') border-primary-600 @enderror">
                    @error('clientFirstName')<p class="mt-2 text-[12px] text-primary-300">{{ $message }}</p>@enderror
                </div>

                {{-- Отчество (по желанию) --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Отчество</label>
                    <input wire:model.blur="clientMiddleName" type="text" placeholder="Иванович"
                           class="w-full bg-transparent border-0 border-b border-ink-700 focus:border-primary-500 focus:outline-none text-[16px] text-ink-100 py-2 transition-colors @error('clientMiddleName') border-primary-600 @enderror">
                    @error('clientMiddleName')<p class="mt-2 text-[12px] text-primary-300">{{ $message }}</p>@enderror
                </div>

                {{-- Телефон: статичный префикс +7 и маска (999) 999-99-99 в едином стиле с остальными полями --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5"
                     x-data="{
                        display: '',
                        init() {
                            this.display = @js($clientPhone).replace(/^\+7\s*/, '');
                            this.reformat(false);
                        },
                        reformat(push = true) {
                            let d = this.display.replace(/\D/g, '');
                            if (d.length > 10 && (d[0] === '7' || d[0] === '8')) d = d.slice(1);
                            d = d.slice(0, 10);
                            let f = '';
                            if (d.length > 0) f = '(' + d.slice(0, 3);
                            if (d.length >= 3) f += ')';
                            if (d.length >= 4) f += ' ' + d.slice(3, 6);
                            if (d.length >= 7) f += '-' + d.slice(6, 8);
                            if (d.length >= 9) f += '-' + d.slice(8, 10);
                            this.display = f;
                            if (push) $wire.set('clientPhone', d.length ? '+7 ' + f : '', false);
                        }
                    }">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Телефон*</label>
                    <div class="flex items-center border-b transition-colors @error('clientPhone') border-primary-600 @else border-ink-700 focus-within:border-primary-500 @enderror">
                        <span class="text-[16px] text-ink-400 py-2 pr-1.5 select-none">+7</span>
                        <input
                            x-model="display"
                            @input="reformat()"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel"
                            placeholder="(___) ___-__-__"
                            class="tel-input flex-1 min-w-0 bg-transparent border-0 focus:outline-none focus:ring-0 text-[16px] text-ink-100 py-2">
                    </div>
                    @error('clientPhone')<p class="mt-2 text-[12px] text-primary-300">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5 sm:col-span-2">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">
                        Email <span class="text-primary-400">*</span>
                        <span class="text-ink-400 font-normal normal-case tracking-normal text-[10px] ml-1">— для просмотра истории обслуживания</span>
                    </label>
                    <input wire:model.blur="clientEmail" type="email" required placeholder="ivan@example.com"
                           class="w-full bg-transparent border-0 border-b border-ink-700 focus:border-primary-500 focus:outline-none text-[16px] text-ink-100 py-2 transition-colors @error('clientEmail') border-primary-600 @enderror">
                    @error('clientEmail')<p class="mt-2 text-[12px] text-primary-300">{{ $message }}</p>@enderror
                </div>

                {{-- Марка --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Марка авто</label>
                    <div
                        x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('carBrandId').live,
                            options: @js($this->carBrands->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->values()),
                            get selectedName() {
                                const o = this.options.find(x => x.id == this.selectedId);
                                return o ? o.name : '';
                            },
                            get filtered() {
                                if (!this.search) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o => o.name.toLowerCase().includes(q));
                            },
                            choose(id) { this.selectedId = id; this.open = false; this.search = ''; },
                        }"
                        @keydown.escape.window="open = false"
                        @click.outside="open = false"
                        class="relative">

                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between gap-3 text-left bg-transparent border-0 border-b text-[16px] py-2 transition-colors"
                                :class="open ? 'border-primary-500' : 'border-ink-700 hover:border-ink-500'">
                            <span x-text="selectedName || '— не указана —'"
                                  :class="selectedName ? 'text-ink-100' : 'text-ink-400'"></span>
                            <svg class="w-4 h-4 text-ink-400 transition-transform shrink-0" :class="open ? 'rotate-180 text-ink-100' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="square" stroke-linejoin="miter" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.150ms
                             class="absolute left-0 right-0 top-full mt-1 z-50 bg-ink-800 border border-ink-600 shadow-xl max-h-80 overflow-hidden flex flex-col"
                             x-cloak>
                            <div class="border-b border-ink-700 p-2 bg-ink-900">
                                <input x-model="search" x-ref="searchInput" type="text" placeholder="Поиск марки..." aria-label="Поиск марки"
                                       @keydown.escape.stop="open = false"
                                       class="w-full px-3 py-2 text-[14px] bg-ink-800 border border-ink-700 focus:border-primary-500 focus:outline-none transition-colors font-mono">
                            </div>
                            <div class="overflow-y-auto">
                                <button type="button" @click="choose('')"
                                        class="w-full text-left px-4 py-3 text-[14px] italic transition-colors"
                                        :class="!selectedId ? 'bg-ink-900 text-ink-100' : 'text-ink-400 hover:bg-ink-900'">
                                    — не указана —
                                </button>
                                <template x-for="o in filtered" :key="o.id">
                                    <button type="button" @click="choose(o.id)"
                                            class="w-full text-left px-4 py-3 text-[15px] transition-colors border-t border-ink-800"
                                            :class="o.id == selectedId ? 'bg-ink-900 text-ink-100' : 'text-ink-100 hover:bg-ink-900'">
                                        <span x-text="o.name"></span>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0 && search" class="px-4 py-6 text-center text-ink-400 text-[13px]">
                                    Не найдено
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Модель --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Модель</label>
                    <div
                        wire:key="model-select-{{ $carBrandId ?? 'none' }}"
                        x-data="{
                            open: false,
                            search: '',
                            disabled: {{ $carBrandId ? 'false' : 'true' }},
                            selectedId: @entangle('carModelId').live,
                            options: @js($this->carModels->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values()),
                            get selectedName() {
                                const o = this.options.find(x => x.id == this.selectedId);
                                return o ? o.name : '';
                            },
                            get filtered() {
                                if (!this.search) return this.options;
                                const q = this.search.toLowerCase();
                                return this.options.filter(o => o.name.toLowerCase().includes(q));
                            },
                            choose(id) { this.selectedId = id; this.open = false; this.search = ''; },
                        }"
                        @keydown.escape.window="open = false"
                        @click.outside="open = false"
                        class="relative"
                        :class="disabled ? 'opacity-40 pointer-events-none' : ''">

                        <button type="button" @click="!disabled && (open = !open)" :disabled="disabled"
                                class="w-full flex items-center justify-between gap-3 text-left bg-transparent border-0 border-b text-[16px] py-2 transition-colors"
                                :class="open ? 'border-primary-500' : 'border-ink-700 hover:border-ink-500'">
                            <span x-text="selectedName || (disabled ? 'Сначала выберите марку' : '— не указана —')"
                                  :class="selectedName ? 'text-ink-100' : 'text-ink-400'"></span>
                            <svg class="w-4 h-4 text-ink-400 transition-transform shrink-0" :class="open ? 'rotate-180 text-ink-100' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="square" stroke-linejoin="miter" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.150ms
                             class="absolute left-0 right-0 top-full mt-1 z-50 bg-ink-800 border border-ink-600 shadow-xl max-h-80 overflow-hidden flex flex-col"
                             x-cloak>
                            <div class="border-b border-ink-700 p-2 bg-ink-900">
                                <input x-model="search" type="text" placeholder="Поиск модели..." aria-label="Поиск модели"
                                       @keydown.escape.stop="open = false"
                                       class="w-full px-3 py-2 text-[14px] bg-ink-800 border border-ink-700 focus:border-primary-500 focus:outline-none transition-colors font-mono">
                            </div>
                            <div class="overflow-y-auto">
                                <button type="button" @click="choose('')"
                                        class="w-full text-left px-4 py-3 text-[14px] italic transition-colors"
                                        :class="!selectedId ? 'bg-ink-900 text-ink-100' : 'text-ink-400 hover:bg-ink-900'">
                                    — не указана —
                                </button>
                                <template x-for="o in filtered" :key="o.id">
                                    <button type="button" @click="choose(o.id)"
                                            class="w-full text-left px-4 py-3 text-[15px] transition-colors border-t border-ink-800"
                                            :class="o.id == selectedId ? 'bg-ink-900 text-ink-100' : 'text-ink-100 hover:bg-ink-900'">
                                        <span x-text="o.name"></span>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0 && search" class="px-4 py-6 text-center text-ink-400 text-[13px]">
                                    Не найдено
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Описание --}}
                <div class="bg-ink-800 border border-ink-700 focus-within:border-primary-500/60 transition-colors p-5 sm:col-span-2">
                    <label class="block text-[12.5px] font-semibold tracking-wide text-ink-300 mb-3">Описание проблемы <span class="text-ink-400 font-normal normal-case tracking-normal text-[10px] ml-1">— необязательно</span></label>
                    <textarea wire:model.blur="problemDescription" rows="3" placeholder="Опишите, что случилось с автомобилем..."
                              class="w-full bg-transparent border-0 border-b border-ink-700 focus:border-primary-500 focus:outline-none text-[15px] text-ink-100 py-2 transition-colors resize-none"></textarea>
                </div>
            </div>
        </div>
        @endif

        {{-- ── ШАГ 4: ПОДТВЕРЖДЕНИЕ ── --}}
        @if($step === 4)
        <div class="{{ $stepDirection === 'back' ? 'wizard-step-back' : 'wizard-step-fwd' }}" wire:key="step-4">

            <h2 class="font-display font-extrabold text-[clamp(1.6rem,4vw,2.5rem)] tracking-tight text-ink-100 mb-2 text-balance">Проверьте данные</h2>
            <p class="text-ink-400 text-[14px] mb-10">Убедитесь, что всё верно, и нажмите «Подтвердить запись».</p>

            <div class="space-y-0 border-t border-ink-700">

                {{-- Услуги --}}
                <div class="border-b border-ink-700 py-6">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="eyebrow-muted">Услуги</div>
                            <div class="font-mono text-[12px] text-ink-400 mt-1 num-tabular">{{ count($serviceIds) }} шт.</div>
                        </div>
                        <ul class="col-span-12 sm:col-span-9 space-y-2">
                            @foreach($this->selectedServices as $service)
                            <li class="flex items-baseline justify-between gap-4">
                                <span class="text-[15px] text-ink-100">{{ $service->name }}</span>
                                @if($service->price)
                                <span class="font-mono text-[13px] text-ink-400 num-tabular shrink-0">от {{ number_format($service->price, 0, '.', ' ') }} ₽</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Дата / Филиал --}}
                <div class="border-b border-ink-700 py-6">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="eyebrow-muted">Когда и где</div>
                        </div>
                        <div class="col-span-12 sm:col-span-9 space-y-1">
                            @if($this->selectedSlot)
                            <div class="font-display font-bold text-lg text-ink-100">
                                {{ $this->selectedSlot->starts_at->isoFormat('D MMMM YYYY') }}
                                <span class="font-mono font-medium text-ink-400 ml-2">{{ $this->selectedSlot->starts_at->format('H:i') }}</span>
                            </div>
                            @endif
                            @if($this->selectedBranch)
                            <div class="text-[14px] text-ink-200">{{ $this->selectedBranch->name }}</div>
                            @if($this->selectedBranch->address)
                            <div class="text-[13px] text-ink-400">{{ $this->selectedBranch->city ? $this->selectedBranch->city . ', ' : '' }}{{ $this->selectedBranch->address }}</div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Контакты --}}
                <div class="border-b border-ink-700 py-6">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="eyebrow-muted">Контакты</div>
                        </div>
                        <dl class="col-span-12 sm:col-span-9 grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-[14px]">
                            <div><dt class="text-ink-400 inline">ФИО:</dt> <dd class="text-ink-100 inline font-medium">{{ $this->clientFullName }}</dd></div>
                            <div><dt class="text-ink-400 inline">Тел:</dt> <dd class="text-ink-100 inline font-mono">{{ $clientPhone }}</dd></div>
                            @if($clientEmail)<div class="sm:col-span-2"><dt class="text-ink-400 inline">Email:</dt> <dd class="text-ink-100 inline">{{ $clientEmail }}</dd></div>@endif
                            @if($carBrandId)
                            @php $brand = $this->carBrands->find($carBrandId); $model = $this->carModels->find($carModelId); @endphp
                            <div class="sm:col-span-2"><dt class="text-ink-400 inline">Авто:</dt> <dd class="text-ink-100 inline">{{ $brand?->name }}{{ $model ? ' ' . $model->name : '' }}</dd></div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($problemDescription)
                <div class="border-b border-ink-700 py-6">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="eyebrow-muted">Описание</div>
                        </div>
                        <div class="col-span-12 sm:col-span-9 text-[14px] text-ink-200 leading-relaxed">
                            {{ $problemDescription }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <p class="mt-8 text-[12px] text-ink-400 leading-relaxed">
                Нажимая «Подтвердить», вы соглашаетесь на обработку персональных данных в целях оформления записи.
            </p>
        </div>
        @endif

        {{-- ═════ КНОПКИ ═════ --}}
        <div class="mt-12 lg:mt-16 pt-8 border-t border-ink-700 flex items-center justify-between gap-3">
            @if($step > 1)
            <button wire:click="prevStep" wire:loading.attr="disabled"
                    class="group inline-flex items-center gap-3 px-5 py-4 text-ink-200 hover:text-ink-100 transition-colors text-[12px] font-bold uppercase tracking-widest">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
                Назад
            </button>
            @else
            <a href="{{ url('/') }}" class="text-ink-400 hover:text-ink-100 transition-colors text-[12px] font-bold uppercase tracking-widest">← Отмена</a>
            @endif

            @if($step < 4)
            <button wire:click="nextStep" wire:loading.attr="disabled"
                    class="group inline-flex items-center justify-between gap-12 px-7 py-5 bg-ink-800 border border-ink-700 hover:bg-primary-700 hover:border-primary-700 text-ink-100 hover:text-white transition-colors duration-300 min-w-[240px] disabled:opacity-50">
                <span class="text-[12px] font-bold uppercase tracking-widest" wire:loading.remove wire:target="nextStep">Далее</span>
                <span class="text-[12px] font-bold uppercase tracking-widest" wire:loading wire:target="nextStep">Загрузка...</span>
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </button>
            @else
            <button wire:click="submit" wire:loading.attr="disabled"
                    class="group inline-flex items-center justify-between gap-12 px-7 py-5 bg-primary-600 hover:bg-primary-700 text-white transition-colors duration-300 min-w-[280px] disabled:opacity-60">
                <span class="text-[12px] font-bold uppercase tracking-widest" wire:loading.remove wire:target="submit">Подтвердить запись</span>
                <span class="text-[12px] font-bold uppercase tracking-widest" wire:loading wire:target="submit">Отправляем...</span>
                <svg wire:loading.remove wire:target="submit" class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12.75l4 4 9-9"/></svg>
                <svg wire:loading wire:target="submit" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </button>
            @endif
        </div>

        {{-- Помощь --}}
        <div class="mt-10 text-[12px] text-ink-400">
            Возникли сложности? <a href="tel:+78000000000" class="text-ink-100 font-mono hover:text-primary-400 transition-colors">+7 800 000 00 00</a>
        </div>

        @endif

    </div>
</div>
