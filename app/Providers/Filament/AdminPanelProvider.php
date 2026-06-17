<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\MyProfile;
use App\Filament\Support\NavigationGlobalSearchProvider;
use App\Filament\Widgets\LatestAppointmentsWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->profile(EditProfile::class)
            // Пункт «Мой профиль» в меню пользователя ведёт на страницу просмотра,
            // а уже оттуда — кнопка «Редактировать профиль».
            ->userMenuItems([
                Action::make('profile')
                    ->label('Мой профиль')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => MyProfile::getUrl()),
            ])
            ->spa()
            ->font('Inter')
            ->brandLogo(asset('storage/logo/logoo.svg'))
            ->favicon(asset('favicon.ico'))

            // ─── Тема ────────────────────────────────────────────────────
            ->darkMode(true)
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'info' => Color::Cyan,
            ])

            // ─── Лейаут ───────────────────────────────────────────────────
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->maxContentWidth('full')

            // Поиск в шапке ищет по РАЗДЕЛАМ (ресурсы/страницы), а не по записям.
            ->globalSearch(NavigationGlobalSearchProvider::class)
            ->databaseNotifications()
            // Чаще опрашиваем уведомления, чтобы счётчик-бейдж и звук срабатывали
            // живо (без перезагрузки). 30с давали ощущение «пришло только после F5».
            ->databaseNotificationsPolling('10s')

            // ─── Живое обновление счётчиков (бейджей) в меню ──────────────
            // Каждые 30с переспрашиваем сайдбар: он пересчитывает бейджи
            // (Заказы, Заявки, Запросы деталей) без перезагрузки страницы
            // и не трогая открытую форму/таблицу. Защита от дублирования
            // интервала при SPA-навигации (wire:navigate).
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <script>
                        (function () {
                            if (window.__sidebarBadgePoll) {
                                clearInterval(window.__sidebarBadgePoll);
                            }
                            window.__sidebarBadgePoll = setInterval(function () {
                                if (window.Livewire) {
                                    window.Livewire.dispatch('refresh-sidebar');
                                }
                            }, 30000);
                        })();
                    </script>
                    HTML),
            )

            // ─── Звуковое оповещение о новых уведомлениях ────────────────
            // Filament-уведомления приходят как database notifications и видны
            // счётчиком-бейджем на колокольчике (опрос wire:poll). Скрипт ловит
            // РОСТ числа непрочитанных и проигрывает короткий «динь» через Web
            // Audio — без аудиофайла и зависимостей. Звук стартует после первого
            // клика/нажатия (политика автоплея браузеров). Отключить:
            // localStorage.setItem('aisNotifSound','off').
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <script>
                        (function () {
                            if (window.__aisNotifSound) return;
                            window.__aisNotifSound = true;

                            var audioCtx = null, lastCount = null;

                            function unlock() {
                                try {
                                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                                    if (audioCtx.state === 'suspended') audioCtx.resume();
                                } catch (e) {}
                                // Заодно по первому действию просим разрешение на
                                // системные уведомления (нужно для «отошёл от стола»).
                                try {
                                    if ('Notification' in window && Notification.permission === 'default') {
                                        Notification.requestPermission();
                                    }
                                } catch (e) {}
                            }
                            window.addEventListener('pointerdown', unlock, true);
                            window.addEventListener('keydown', unlock, true);

                            // Заголовок верхнего НЕпрочитанного уведомления — читаем из
                            // списка в DOM (он отрендерен, даже когда панель закрыта).
                            function latestTitle() {
                                try {
                                    var item = document.querySelector('.fi-no-notification-unread-ctn');
                                    if (!item) return '';
                                    var t = item.querySelector('.fi-no-notification-title');
                                    return t ? (t.textContent || '').trim() : '';
                                } catch (e) { return ''; }
                            }

                            // Десктопное (системное) уведомление — всплывает в углу экрана
                            // даже когда вкладка админки в фоне/браузер свёрнут. Показываем
                            // только когда окно НЕ в фокусе (если приёмщик смотрит в АИС —
                            // достаточно колокольчика и звука, дубль не нужен).
                            function desktopNotify(count) {
                                try {
                                    if (!('Notification' in window) || Notification.permission !== 'granted') return;
                                    if (document.hasFocus()) return;
                                    var title = latestTitle();
                                    var body = title || ('Новое уведомление' + (count ? ' · непрочитано: ' + count : ''));
                                    var n = new Notification('АИС «Автосервис»', {
                                        body: body,
                                        tag: 'ais-notification',
                                        renotify: true,
                                    });
                                    n.onclick = function () { window.focus(); this.close(); };
                                } catch (e) {}
                            }

                            function beep() {
                                if (localStorage.getItem('aisNotifSound') === 'off') return;
                                unlock();
                                if (!audioCtx) return;
                                var t0 = audioCtx.currentTime;
                                [[988, 0], [1319, 0.13]].forEach(function (p) {
                                    var o = audioCtx.createOscillator(), g = audioCtx.createGain();
                                    o.type = 'sine';
                                    o.frequency.value = p[0];
                                    var s = t0 + p[1];
                                    g.gain.setValueAtTime(0.0001, s);
                                    g.gain.exponentialRampToValueAtTime(0.18, s + 0.02);
                                    g.gain.exponentialRampToValueAtTime(0.0001, s + 0.28);
                                    o.connect(g).connect(audioCtx.destination);
                                    o.start(s);
                                    o.stop(s + 0.3);
                                });
                            }

                            function readCount() {
                                var btn = document.querySelector('.fi-topbar-database-notifications-btn');
                                if (!btn) return 0;
                                var badge = btn.querySelector('.fi-icon-btn-badge-ctn');
                                if (!badge) return 0;
                                var n = parseInt((badge.textContent || '').replace(/\D/g, ''), 10);
                                return isNaN(n) ? 0 : n;
                            }

                            // Лимит повторов: не более REMIND_MAX напоминаний на одну
                            // «волну» непрочитанных, чтобы не превратить это в пытку.
                            // Сбрасывается при новом уведомлении и когда всё прочитано.
                            var REMIND_EVERY = 60000, REMIND_MAX = 3, remindUsed = 0;

                            // Детектор НОВОГО уведомления — мгновенный «динь» + сброс лимита.
                            function check() {
                                var c = readCount();
                                if (lastCount === null) { lastCount = c; return; } // первый замер — тихо
                                if (c > lastCount) { beep(); desktopNotify(c); remindUsed = 0; } // новое — сразу
                                else if (c === 0) { remindUsed = 0; }               // прочитали всё
                                lastCount = c;
                            }
                            setInterval(check, 3000);

                            // Напоминания: раз в REMIND_EVERY смотрим — есть непрочитанные?
                            // Если да и лимит не исчерпан — повторяем «динь». Работает
                            // независимо от того, поймали мы момент прихода или нет
                            // (даже после перезагрузки страницы).
                            setInterval(function () {
                                if (readCount() > 0 && remindUsed < REMIND_MAX) {
                                    remindUsed++;
                                    beep();
                                    desktopNotify(readCount());
                                }
                            }, REMIND_EVERY);

                            // Ручная проверка звука из консоли: aisNotifBeep()
                            // (предварительно кликни по странице — иначе браузер
                            // держит звук заблокированным до действия пользователя).
                            window.aisNotifBeep = beep;
                        })();
                    </script>
                    HTML),
            )

            // ─── Верхняя навигация ───────────────────────────────────────
            // Справочники вынесены из левого сайдбара в верхнюю полосу и
            // разбиты на группы-выпадашки (Каталог услуг, Автосправочник,
            // Персонал, Организация). Ресурсы помечены трейтом
            // HiddenFromSidebarNav, состав групп — App\Filament\Support\TopNavigation.
            // Хук LOGO_AFTER ставит меню сразу после названия панели.
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => view('filament.top-navigation')->render(),
            )

            // ─── Группы навигации (порядок и иконки) ─────────────────────
            // Сведено к трём группам: ежедневное наверху, редкие настройки
            // и справочники — в одной свёрнутой группе «Настройки».
            ->navigationGroups([
                NavigationGroup::make('Работа')
                    ->icon('heroicon-o-bolt'),

                NavigationGroup::make('Склад')
                    ->icon('heroicon-o-archive-box'),

                NavigationGroup::make('Отчёты')
                    ->icon('heroicon-o-chart-bar'),

                NavigationGroup::make('Права доступа')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsed(),
            ])

            // ─── Ресурсы и страницы ───────────────────────────────────────
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                StatsOverviewWidget::class,
                LatestOrdersWidget::class,
                LatestAppointmentsWidget::class,
            ])

            // ─── Middleware ───────────────────────────────────────────────
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make());
    }
}
