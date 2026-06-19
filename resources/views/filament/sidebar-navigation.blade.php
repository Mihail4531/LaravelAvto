@php
    use App\Filament\Support\TopNavigation;

    $groups = TopNavigation::visibleGroups();
@endphp

@if (filled($groups))
    {{-- Справочники (на десктопе они в верхнем меню). На планшете/телефоне,
         где верхнее меню скрыто, показываем их здесь — в боковом меню. --}}
    <div class="ais-sidebar-extra">
        <style>
            .ais-sidebar-extra { display: none; }
            @media (max-width: 1024px) {
                .ais-sidebar-extra {
                    display: block;
                    margin-top: 0.5rem;
                    padding-top: 0.75rem;
                    border-top: 1px solid rgb(229 231 235);
                }
                .dark .ais-sidebar-extra { border-top-color: rgba(255, 255, 255, 0.08); }
            }
            .ais-sb-section-label {
                padding: 0.25rem 0.75rem 0.5rem;
                font-size: 0.6875rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: rgb(107 114 128);
            }
            .dark .ais-sb-section-label { color: rgb(156 163 175); }
            .ais-sb-group { margin-bottom: 0.25rem; }
            .ais-sb-group-label {
                display: flex; align-items: center; gap: 0.5rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.8125rem; font-weight: 600;
                color: rgb(55 65 81);
            }
            .dark .ais-sb-group-label { color: rgb(209 213 219); }
            .ais-sb-group-label svg { width: 1.25rem; height: 1.25rem; opacity: 0.7; }
            .ais-sb-items { list-style: none; margin: 0; padding: 0 0 0 0.5rem; }
            .ais-sb-item {
                display: flex; align-items: center; gap: 0.625rem;
                padding: 0.5rem 0.75rem;
                border-radius: 0.5rem;
                font-size: 0.875rem; font-weight: 500;
                color: rgb(75 85 99);
                text-decoration: none;
                transition: background 0.15s, color 0.15s;
            }
            .ais-sb-item svg { width: 1.1rem; height: 1.1rem; flex-shrink: 0; opacity: 0.65; }
            .ais-sb-item:hover { background: rgba(0, 0, 0, 0.05); color: rgb(17 24 39); }
            .ais-sb-item.is-active { background: rgba(99, 102, 241, 0.1); color: rgb(67 56 202); font-weight: 600; }
            .ais-sb-item.is-active svg { opacity: 1; }
            .dark .ais-sb-item { color: rgb(156 163 175); }
            .dark .ais-sb-item:hover { background: rgba(255, 255, 255, 0.06); color: #fff; }
            .dark .ais-sb-item.is-active { background: rgba(99, 102, 241, 0.18); color: rgb(199 210 254); }
        </style>

        <div class="ais-sb-section-label">Справочники</div>

        @foreach ($groups as $group)
            <div class="ais-sb-group">
                <div class="ais-sb-group-label">
                    <x-filament::icon :icon="$group['icon']" />
                    {{ $group['label'] }}
                </div>
                <ul class="ais-sb-items">
                    @foreach ($group['items'] as $item)
                        <li>
                            <a href="{{ $item['url'] }}" wire:navigate
                               class="ais-sb-item {{ $item['active'] ? 'is-active' : '' }}">
                                <x-filament::icon :icon="$item['icon']" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endif
