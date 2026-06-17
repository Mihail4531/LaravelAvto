<div>
    @if($categories->isEmpty())
        <div class="bg-ink-800 border border-ink-700 text-center py-20">
            <p class="text-ink-400">Каталог услуг ещё не настроен</p>
        </div>
    @else

    @php
        $allServices = $categories->flatMap(fn ($cat) =>
            $cat->services->map(fn ($s) => (object) [
                'id'               => $s->id,
                'name'             => $s->name,
                'description'      => $s->description,
                'price'            => (float) $s->price,
                'duration_minutes' => $s->duration_minutes,
                'image'            => $s->image,
                'category_id'      => $cat->id,
            ])
        );

        // Полные данные для модалки «Подробнее» и поиска
        $catalogJson = $categories->flatMap(fn ($cat) =>
            $cat->services->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'description' => $s->description,
                'price'       => (float) $s->price,
                'duration'    => $s->duration_minutes,
                'image'       => $s->image ? asset('storage/'.$s->image) : null,
                'category'    => $cat->name,
                'category_id' => $cat->id,
            ])
        )->values();
        $bookingUrl = route('booking');
    @endphp

    <div x-data="{
            activeCategory: 'all',
            query: '',
            modalId: null,
            limit: 3,
            baseLimit: 3,
            catalog: @js($catalogJson),
            bookingUrl: @js($bookingUrl),
            init() {
                // На широких экранах сразу показываем больше; на телефоне — всего 3
                this.baseLimit = window.matchMedia('(min-width: 1024px)').matches ? 9 : 3;
                this.limit = this.baseLimit;
                // Смена категории или новый поиск — сбрасываем «Показать ещё»
                this.$watch('query', () => { this.limit = this.baseLimit; });
                this.$watch('activeCategory', () => { this.limit = this.baseLimit; });
            },
            get current() { return this.catalog.find(s => s.id === this.modalId) || {}; },
            openDetails(id) { this.modalId = id; document.body.style.overflow = 'hidden'; },
            closeDetails() { this.modalId = null; document.body.style.overflow = ''; },
            fmt(n) { return new Intl.NumberFormat('ru-RU').format(n); },
            matches(id) {
                const q = this.query.trim().toLowerCase();
                if (!q) return true;
                const s = this.catalog.find(x => x.id === id);
                if (!s) return false;
                return (s.name || '').toLowerCase().includes(q) || (s.description || '').toLowerCase().includes(q);
            },
            visible(catId, id) {
                if (this.query.trim()) return this.matches(id);
                return this.activeCategory === 'all' || this.activeCategory === catId;
            },
            // id видимых услуг в порядке каталога (= порядок в DOM)
            orderedVisibleIds() {
                return this.catalog.filter(s => this.visible(s.category_id ?? s.categoryId, s.id)).map(s => s.id);
            },
            // показываем только первые `limit` из отфильтрованных
            withinLimit(catId, id) {
                if (!this.visible(catId, id)) return false;
                return this.orderedVisibleIds().indexOf(id) < this.limit;
            },
            get visibleCount() {
                return this.catalog.filter(s => this.visible(s.category_id ?? s.categoryId, s.id)).length;
            },
         }"
         @keydown.escape.window="closeDetails()">

        {{-- ── Табы категорий + поиск ───────────────────────────────── --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-10 lg:mb-12">

            {{-- Табы: на мобильном — горизонтальная лента (свайп), на десктопе — перенос --}}
            <div class="flex gap-2 overflow-x-auto lg:flex-wrap lg:overflow-visible -mx-5 px-5 lg:mx-0 lg:px-0 pb-1 lg:pb-0 hide-scrollbar">
                <button type="button"
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all'
                            ? 'bg-primary-500 text-white border-primary-500'
                            : 'text-ink-300 border-ink-700 hover:border-ink-500 hover:text-primary-600'"
                        class="px-5 py-2.5 border text-[13px] font-semibold whitespace-nowrap shrink-0 transition-colors">
                    Все услуги
                </button>

                @foreach($categories as $cat)
                <button type="button"
                        @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }}
                            ? 'bg-primary-500 text-white border-primary-500'
                            : 'text-ink-300 border-ink-700 hover:border-ink-500 hover:text-primary-600'"
                        class="px-5 py-2.5 border text-[13px] font-semibold whitespace-nowrap shrink-0 transition-colors">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>

            {{-- Поиск --}}
            <div class="relative shrink-0 lg:w-72">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" x-model="query" placeholder="Поиск услуги..." aria-label="Поиск услуги"
                       class="w-full bg-ink-800 border border-ink-700 focus:border-primary-500 outline-none pl-10 pr-9 py-2.5 text-[14px] text-ink-100 placeholder:text-ink-500 transition-colors">
                <button type="button" x-show="query" x-cloak @click="query = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-ink-400 hover:text-ink-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- ── Сетка карточек услуг ─────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 lg:gap-6">
            @foreach($allServices as $service)
            <article x-show="withinLimit({{ $service->category_id }}, {{ $service->id }})"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                 class="group relative bg-ink-800 overflow-hidden border border-ink-700 hover:border-primary-500/60 transition-all duration-400 flex flex-row sm:flex-col hover:-translate-y-1 hover:shadow-[0_18px_40px_-22px_rgba(13,30,55,0.30)]">

                {{-- Hero-зона: на узких экранах — компактная миниатюра слева, от sm — фото сверху --}}
                <div class="relative shrink-0 w-28 sm:w-full aspect-square sm:aspect-[16/10] overflow-hidden bg-gradient-to-br from-ink-700 to-ink-900">
                    @if($service->image)
                        <img src="{{ asset('storage/'.$service->image) }}"
                             alt="{{ $service->name }}"
                             loading="lazy"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                        {{-- Чистый плейсхолдер: тонкая точечная текстура + крупная иконка (без размытия) --}}
                        <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(0,102,179,0.12) 1px, transparent 1px); background-size: 22px 22px;"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="relative w-10 h-10 sm:w-16 sm:h-16 text-primary-500/35 transition-transform duration-500 group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Бейдж цены сверху-слева --}}
                    @if($service->price > 0)
                        <div class="absolute top-3 left-3 px-3 py-1.5 bg-white border border-ink-700 shadow-sm rounded-full hidden sm:flex items-baseline gap-1.5">
                            <span class="text-[10px] text-ink-400 uppercase tracking-wider font-semibold">от</span>
                            <span class="font-mono font-bold text-ink-100 text-[13px]">{{ number_format($service->price, 0, ',', ' ') }} ₽</span>
                        </div>
                    @else
                        <div class="absolute top-3 left-3 px-3 py-1.5 bg-success-500 text-ink-900 rounded-full hidden sm:flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-ink-900 rounded-full"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider">Бесплатно</span>
                        </div>
                    @endif

                    {{-- Бейдж длительности справа --}}
                    @if($service->duration_minutes)
                        <div class="absolute top-3 right-3 px-2.5 py-1.5 bg-white border border-ink-700 shadow-sm rounded-full hidden sm:flex items-center gap-1">
                            <svg class="w-3 h-3 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-mono text-[11px] text-ink-200">{{ $service->duration_minutes }} мин</span>
                        </div>
                    @endif
                </div>

                {{-- Контент карточки --}}
                <div class="p-3.5 sm:p-6 lg:p-7 flex-1 flex flex-col min-w-0">
                    <h3 class="font-display font-bold text-base sm:text-xl text-ink-100 tracking-tight mb-1 sm:mb-3 leading-snug line-clamp-2 sm:line-clamp-none">
                        {{ $service->name }}
                    </h3>

                    {{-- Мобильная мета: цена и длительность строкой (бейджи поверх фото на узких экранах скрыты) --}}
                    <div class="flex sm:hidden items-center gap-1.5 text-[12px] mb-2.5">
                        @if($service->price > 0)
                            <span class="font-mono font-bold text-ink-100">от {{ number_format($service->price, 0, ',', ' ') }} ₽</span>
                        @else
                            <span class="font-bold text-success-700">Бесплатно</span>
                        @endif
                        @if($service->duration_minutes)
                            <span class="w-1 h-1 rounded-full bg-ink-600 shrink-0"></span>
                            <span class="font-mono text-ink-400">{{ $service->duration_minutes }} мин</span>
                        @endif
                    </div>

                    @if($service->description)
                    <p class="hidden sm:block text-ink-400 text-[14px] leading-relaxed mb-6 line-clamp-3">
                        {{ $service->description }}
                    </p>
                    @endif

                    {{-- Действия: на узких экранах — иконка «Подробнее» + растянутая «Записаться»;
                         от sm — две равные кнопки --}}
                    <div class="mt-auto pt-1 sm:pt-0 flex sm:grid sm:grid-cols-2 items-stretch gap-2">
                        <button type="button" @click="openDetails({{ $service->id }})"
                                aria-label="Подробнее об услуге «{{ $service->name }}»"
                                class="inline-flex items-center justify-center gap-1.5 shrink-0 w-11 sm:w-auto py-3 px-0 sm:px-3 border border-ink-600 hover:border-primary-500 text-ink-200 hover:text-primary-400 text-[12px] font-bold uppercase tracking-wider transition-all">
                            <svg class="w-4 h-4 sm:w-3.5 sm:h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                            <span class="hidden sm:inline">Подробнее</span>
                        </button>
                        <a href="{{ route('booking') }}?service={{ $service->id }}"
                           class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 py-3 px-3 bg-primary-500 hover:bg-primary-600 text-white border border-primary-500 text-[12px] font-bold uppercase tracking-wider transition-all group/btn">
                            Записаться
                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Управление длиной списка: «Показать ещё» (пока есть скрытые) и «Свернуть»
             (когда раскрыто сверх стартового). Ограничиваем каталог, чтобы при большом
             числе услуг список не растягивался бесконечно, особенно на телефоне. --}}
        <div x-show="visibleCount > baseLimit" x-cloak class="mt-8 lg:mt-10 flex flex-wrap items-center justify-center gap-3">
            <button type="button" x-show="visibleCount > limit" @click="limit += baseLimit" class="btn-ghost group/more">
                Показать ещё
                <span class="font-mono normal-case tracking-normal text-ink-400" x-text="'(' + (visibleCount - limit) + ')'"></span>
                <svg class="w-4 h-4 transition-transform group-hover/more:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <button type="button" x-show="limit > baseLimit" x-cloak
                    @click="limit = baseLimit; $nextTick(() => document.getElementById('services')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
                    class="btn-ghost group/less">
                Свернуть
                <svg class="w-4 h-4 transition-transform group-hover/less:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                </svg>
            </button>
        </div>

        @foreach($categories as $cat)
            @if($cat->services->isEmpty())
            <div x-show="!query && activeCategory === {{ $cat->id }}"
                 x-cloak
                 class="bg-ink-800 border border-dashed border-ink-700 p-12 text-center">
                <p class="text-ink-400 text-[14px]">В этой категории пока нет услуг.</p>
            </div>
            @endif
        @endforeach

        {{-- Ничего не найдено по поиску --}}
        <div x-show="query && visibleCount === 0" x-cloak
             class="bg-ink-800 border border-dashed border-ink-700 p-12 text-center">
            <svg class="w-10 h-10 text-ink-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <p class="text-ink-300 text-[15px] font-semibold mb-1">Ничего не найдено</p>
            <p class="text-ink-500 text-[13px]">По запросу «<span x-text="query" class="text-primary-400"></span>» услуг нет. Попробуйте другое слово.</p>
        </div>

        {{-- ════════ МОДАЛКА «Подробнее об услуге» ════════ --}}
        <div x-show="modalId" x-cloak
             class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6">

            {{-- Затемнение --}}
            <div x-show="modalId" x-transition.opacity.duration.300ms
                 @click="closeDetails()"
                 class="absolute inset-0 bg-[#0B1220]/65 backdrop-blur-sm"></div>

            {{-- Панель: фото-шапка + прокручиваемое тело + закреплённая снизу кнопка.
                 Колонка с max-h, тело скроллится, CTA всегда виден. --}}
            <div x-show="modalId"
                 x-transition:enter="transition ease-out duration-400"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative w-full max-w-2xl max-h-[90dvh] flex flex-col overflow-hidden card-steel edge-top">

                {{-- Кнопка закрытия --}}
                <button type="button" @click="closeDetails()"
                        class="absolute top-4 right-4 z-20 w-10 h-10 flex items-center justify-center bg-white border border-ink-700 shadow-sm text-ink-300 hover:text-ink-100 hover:border-primary-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                {{-- Прокручиваемая область: шапка + тело --}}
                <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain">

                    {{-- Hero-зона модалки --}}
                    <div class="relative shrink-0 h-44 sm:h-56 overflow-hidden bg-gradient-to-br from-ink-700 to-ink-900">
                        <template x-if="current.image">
                            <img :src="current.image" :alt="current.name" class="absolute inset-0 w-full h-full object-cover">
                        </template>
                        <template x-if="!current.image">
                            <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(0,102,179,0.12) 1px, transparent 1px); background-size: 22px 22px;"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="relative w-20 h-20 text-primary-500/35" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
                                </svg>
                            </div>
                        </template>
                        <div class="absolute inset-0 bg-gradient-to-t from-ink-800 via-ink-800/20 to-transparent"></div>
                    </div>

                    {{-- Тело --}}
                    <div class="p-6 sm:p-8 -mt-10 relative">
                        <span class="eyebrow mb-3" x-text="current.category"></span>
                        <h3 class="font-display font-extrabold text-2xl sm:text-3xl text-ink-100 tracking-tight leading-tight mb-5" x-text="current.name"></h3>

                        {{-- Метрики --}}
                        <div class="flex flex-wrap gap-3 mb-6">
                            <div class="flex items-center gap-2 px-4 py-2 bg-ink-900 border border-ink-700">
                                <span class="text-[10px] text-ink-400 uppercase tracking-wider font-semibold">Цена</span>
                                <span class="font-mono font-bold text-ink-100 text-[14px]">
                                    <template x-if="current.price > 0"><span><span class="text-ink-400 text-[11px]">от</span> <span x-text="fmt(current.price)"></span> ₽</span></template>
                                    <template x-if="!current.price"><span class="text-success-500">Бесплатно</span></template>
                                </span>
                            </div>
                            <template x-if="current.duration">
                                <div class="flex items-center gap-2 px-4 py-2 bg-ink-900 border border-ink-700">
                                    <svg class="w-3.5 h-3.5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="font-mono text-[13px] text-ink-200">~ <span x-text="current.duration"></span> мин</span>
                                </div>
                            </template>
                        </div>

                        {{-- Описание --}}
                        <div class="border-t border-ink-700 pt-5">
                            <div class="eyebrow-muted mb-3">Описание</div>
                            <p class="text-ink-200 text-[15px] leading-relaxed whitespace-pre-line"
                               x-text="current.description || 'Подробное описание этой услуги скоро появится. Запишитесь — мастер расскажет детали и согласует объём работ.'"></p>
                        </div>
                    </div>
                </div>

                {{-- Закреплённая снизу кнопка — видна всегда --}}
                <div class="shrink-0 p-4 sm:p-6 border-t border-ink-700 bg-ink-800">
                    <a :href="bookingUrl + '?service=' + modalId"
                       class="w-full inline-flex items-center justify-center gap-2 py-4 bg-primary-500 hover:bg-primary-400 text-white text-[13px] font-bold uppercase tracking-wider transition-colors">
                        Записаться на эту услугу
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>

    @endif
</div>
