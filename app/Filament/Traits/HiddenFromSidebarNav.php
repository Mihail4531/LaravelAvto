<?php

namespace App\Filament\Traits;

/**
 * Помечает ресурс как вынесенный в ВЕРХНЮЮ навигацию: убирает его из
 * левого сайдбара. Сами пункты рендерятся выпадающим меню «Настройки»
 * в верхней полосе — см. App\Filament\Support\TopNavigation и
 * resources/views/filament/top-navigation.blade.php.
 *
 * При добавлении/удалении ресурса не забудьте синхронизировать список
 * в App\Filament\Support\TopNavigation::RESOURCES.
 */
trait HiddenFromSidebarNav
{
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
