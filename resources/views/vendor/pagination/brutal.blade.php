@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">

    {{-- Назад --}}
    @if ($paginator->onFirstPage())
        <span class="inline-flex items-center gap-3 px-2 py-3 text-stone-300 cursor-not-allowed text-[12px] font-bold uppercase tracking-widest select-none">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
            <span class="hidden sm:inline">Назад</span>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
           class="group inline-flex items-center gap-3 px-2 py-3 text-stone-700 hover:text-stone-950 text-[12px] font-bold uppercase tracking-widest transition-colors">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
            <span class="hidden sm:inline">Назад</span>
        </a>
    @endif

    {{-- Номера страниц --}}
    <div class="flex items-stretch gap-px">
        @for ($p = 1; $p <= $paginator->lastPage(); $p++)
            @if ($p === $paginator->currentPage())
                <span aria-current="page"
                      class="min-w-[44px] px-3 py-3 bg-stone-950 text-white font-mono text-[13px] num-tabular text-center leading-none flex items-center justify-center">
                    {{ str_pad($p, 2, '0', STR_PAD_LEFT) }}
                </span>
            @else
                <a href="{{ $paginator->url($p) }}"
                   class="min-w-[44px] px-3 py-3 bg-stone-100 hover:bg-stone-950 text-stone-700 hover:text-white font-mono text-[13px] num-tabular text-center leading-none flex items-center justify-center transition-colors">
                    {{ str_pad($p, 2, '0', STR_PAD_LEFT) }}
                </a>
            @endif
        @endfor
    </div>

    {{-- Вперёд --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
           class="group inline-flex items-center gap-3 px-2 py-3 text-stone-700 hover:text-stone-950 text-[12px] font-bold uppercase tracking-widest transition-colors">
            <span class="hidden sm:inline">Вперёд</span>
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
        </a>
    @else
        <span class="inline-flex items-center gap-3 px-2 py-3 text-stone-300 cursor-not-allowed text-[12px] font-bold uppercase tracking-widest select-none">
            <span class="hidden sm:inline">Вперёд</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="square" stroke-linejoin="miter" d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
        </span>
    @endif

</nav>
@endif
