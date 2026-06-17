@php
    use App\Filament\Support\TopNavigation;

    $groups = TopNavigation::visibleGroups();
@endphp

@if (filled($groups))
    {{-- Центрируем меню по горизонтали относительно всей верхней полосы. --}}
    <style>
        .fi-topbar { position: relative; }
        .fi-topbar .fi-topnav {
            position: absolute;
            inset-inline-start: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
        }
        @media (max-width: 1024px) {
            /* На узких экранах не центрируем абсолютом — возвращаем в поток. */
            .fi-topbar .fi-topnav {
                position: static;
                transform: none;
            }
        }
    </style>

    <ul class="fi-topbar-nav-groups fi-topnav">
        @foreach ($groups as $group)
            <x-filament::dropdown placement="bottom-start" teleport>
                <x-slot name="trigger">
                    <x-filament-panels::topbar.item
                        :active="$group['active']"
                        :icon="$group['icon']"
                    >
                        {{ $group['label'] }}
                    </x-filament-panels::topbar.item>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach ($group['items'] as $item)
                        <x-filament::dropdown.list.item
                            tag="a"
                            :href="$item['url']"
                            :icon="$item['icon']"
                            :color="$item['active'] ? 'primary' : 'gray'"
                            wire:navigate
                        >
                            {{ $item['label'] }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        @endforeach
    </ul>
@endif
