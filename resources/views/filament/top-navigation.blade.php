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
            /* На планшете/телефоне верхнее меню переполняло топбар, поэтому
               прячем его здесь — те же группы показываем в БОКОВОМ меню
               (resources/views/filament/sidebar-navigation.blade.php,
               render hook SIDEBAR_NAV_END). */
            .fi-topbar .fi-topnav { display: none !important; }
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
