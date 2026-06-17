<?php

namespace App\Filament\Support;

use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;
use Illuminate\Support\Str;

/**
 * Глобальный поиск по РАЗДЕЛАМ (как командное меню), а не по записям в БД.
 *
 * Пишешь «зак» — находит ресурс «Заказы» и предлагает перейти. Учитывает
 * права: показываются только разделы, доступные текущему пользователю
 * (canAccess), включая вынесенные в верхнее меню справочники.
 *
 * Подключается в AdminPanelProvider через ->globalSearch(self::class).
 */
class NavigationGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $query = trim($query);

        if ($query === '') {
            return null;
        }

        $needle = Str::lower($query);
        $results = [];

        // ── Ресурсы (Заказы, Клиенты, Запчасти, справочники и т.д.) ──────────
        foreach (Filament::getResources() as $resource) {
            if (! $resource::canAccess()) {
                continue;
            }

            $label = $resource::getNavigationLabel();

            // Ищем по названию в меню + ед./мн. формам модели.
            $haystack = Str::lower(implode(' ', array_filter([
                $label,
                $resource::getPluralModelLabel(),
                $resource::getModelLabel(),
            ])));

            if (! Str::contains($haystack, $needle)) {
                continue;
            }

            $results[] = new GlobalSearchResult(
                title: $label,
                url: $resource::getUrl(),
            );
        }

        // ── Страницы (Дашборд, Отчёты, Мой профиль и др.) ────────────────────
        foreach (Filament::getPages() as $page) {
            if (! $page::canAccess()) {
                continue;
            }

            $label = $page::getNavigationLabel();

            if (! Str::contains(Str::lower($label), $needle)) {
                continue;
            }

            $results[] = new GlobalSearchResult(
                title: $label,
                url: $page::getUrl(),
            );
        }

        if (empty($results)) {
            return null;
        }

        return GlobalSearchResults::make()->category('Разделы', $results);
    }
}
