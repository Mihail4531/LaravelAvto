<?php

namespace App\Filament\Support;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\CarBrands\CarBrandResource;
use App\Filament\Resources\CarModels\CarModelResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\TimeSlots\TimeSlotResource;
use App\Filament\Resources\Users\UserResource;

/**
 * Источник истины для ВЕРХНЕЙ навигации (справочники).
 *
 * Группы рендерятся выпадашками в верхней полосе (см.
 * resources/views/filament/top-navigation.blade.php). Все перечисленные
 * ресурсы скрыты из левого сайдбара трейтом
 * App\Filament\Traits\HiddenFromSidebarNav.
 *
 * Отчёты и «Права доступа» — НЕ здесь, они остаются в левом сайдбаре.
 */
class TopNavigation
{
    /**
     * Группы верхнего меню. Порядок групп и пунктов = порядок в UI.
     *
     * @return array<int, array{label: string, icon: string, resources: array<int, class-string>}>
     */
    public static function groups(): array
    {
        return [
            [
                'label' => 'Каталог услуг',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'resources' => [
                    ServiceResource::class,
                    CategoryResource::class,
                ],
            ],
            [
                'label' => 'Автосправочник',
                'icon' => 'heroicon-o-truck',
                'resources' => [
                    CarBrandResource::class,
                    CarModelResource::class,
                ],
            ],
            [
                'label' => 'Персонал',
                'icon' => 'heroicon-o-users',
                'resources' => [
                    UserResource::class,
                    PositionResource::class,
                ],
            ],
            [
                'label' => 'Организация',
                'icon' => 'heroicon-o-building-office-2',
                'resources' => [
                    BranchResource::class,
                    TimeSlotResource::class,
                    GalleryItemResource::class,
                ],
            ],
        ];
    }

    /**
     * Группы с подготовленными, отфильтрованными по правам пунктами.
     * Пустые группы (нет доступных пунктов) выбрасываются.
     *
     * @return array<int, array{label: string, icon: string, active: bool, items: array<int, array{label: string, url: string, icon: mixed, active: bool}>}>
     */
    public static function visibleGroups(): array
    {
        $currentUrl = request()->url();
        $groups = [];

        foreach (static::groups() as $group) {
            $items = [];

            foreach ($group['resources'] as $resource) {
                if (! $resource::canViewAny()) {
                    continue;
                }

                $url = $resource::getUrl();

                $items[] = [
                    'label' => $resource::getNavigationLabel(),
                    'url' => $url,
                    'icon' => $resource::getNavigationIcon(),
                    'active' => str($currentUrl)->startsWith($url),
                ];
            }

            if (empty($items)) {
                continue;
            }

            $groups[] = [
                'label' => $group['label'],
                'icon' => $group['icon'],
                'active' => collect($items)->contains('active', true),
                'items' => $items,
            ];
        }

        return $groups;
    }
}
