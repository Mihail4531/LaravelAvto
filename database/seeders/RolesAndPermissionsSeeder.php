<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Ресурсы системы (snake_case). Под каждый создаются стандартные
     * permissions: view_any_*, view_*, create_*, update_*, delete_*.
     */
    private const RESOURCES = [
        'appointment',
        'order',
        'client',
        'car',
        'part',
        'payment',
        'service',
        'category',
        'branch',
        'time_slot',
        'user',
        'position',
        'car_brand',
        'car_model',
        'gallery_item',
        'part_request',
        'role',
    ];

    /**
     * Кастомные permissions, не привязанные к стандартному CRUD.
     */
    private const CUSTOM_PERMISSIONS = [
        'assign_order_executor',     // назначить исполнителя на услугу в заказе
        'change_order_status',       // изменять статус заказа
        'change_own_service_status', // механик меняет статус только своих услуг
        'issue_part',                // выдать запчасть из склада
        'receive_part',              // оприходовать запчасть на склад
        'view_financial_reports',    // финансовые отчёты
        'view_warehouse_reports',    // отчёты по складу
        'access_admin_panel',        // базовый доступ к админ-панели
        'view_all_branches',         // видеть данные всех филиалов (иначе — только свой)
    ];

    /**
     * Роли, удалённые из проекта при упрощении — чистим при сидировании.
     */
    private const OBSOLETE_ROLES = [
        'admin', 'manager', 'accountant',
        'owner', 'senior_receptionist', 'operator',
        'dispatcher', 'diagnostician', 'warehouse_manager',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::whereIn('name', self::OBSOLETE_ROLES)
            ->where('guard_name', 'web')
            ->delete();

        $this->createPermissions();
        $this->createRoles();
        $this->createDefaultAdmin();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Готово: создано '.Permission::count().' permissions и '.Role::count().' ролей.');
        $this->command->info('Супер-админ: логин admin / password');
    }

    private function createPermissions(): void
    {
        foreach (self::RESOURCES as $resource) {
            foreach (['view_any', 'view', 'create', 'update', 'delete'] as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        foreach (self::CUSTOM_PERMISSIONS as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createRoles(): void
    {
        // ─── super_admin: ИТ-администратор ──────────────────────────────────
        // Получает все права через Gate::before (AppServiceProvider).
        // Единственная роль, которой доступен раздел «Роли и разрешения».
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ─── director: Управляющий / Директор СТО ───────────────────────────
        // Верхний бизнес-аккаунт: полный доступ ко всему, ВКЛЮЧАЯ управление
        // ролями (раздел «Роли») — сам создаёт роли и распределяет права через
        // админку. От super_admin отличается тем, что НЕ обходит проверки через
        // Gate::before и не правит «сырой» каталог разрешений — это остаётся за
        // техническим аварийным аккаунтом super_admin (break-glass).
        $director = Role::firstOrCreate(['name' => 'director', 'guard_name' => 'web']);
        $director->syncPermissions(Permission::all());

        // ─── Разграничение по филиалам (см. App\Support\BranchScope) ────────
        // Право view_all_branches есть только у director и super_admin (через
        // Permission::all()). Остальные роли ниже его НЕ получают, поэтому видят
        // операционные данные (заказы, заявки, слоты, запросы склада) только
        // своего филиала. При одном филиале это ни на что не влияет.

        // ─── receptionist: Приёмщик ─────────────────────────────────────────
        // Передний фронт: клиенты, авто, заявки, заказы, оплата.
        // Формирует заказ-наряд и передаёт его старшему мастеру; назначение
        // исполнителя и смену статуса заказа выполняет старший мастер.
        // Видит данные только своего филиала (нет view_all_branches).
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionist->syncPermissions([
            ...$this->crudNoDelete('appointment'),
            ...$this->crudNoDelete('order'),
            ...$this->crudNoDelete('client'),
            ...$this->crudNoDelete('car'),
            'view_any_payment', 'view_payment', 'create_payment',
            ...$this->readOnly('service'),
            ...$this->readOnly('category'),
            ...$this->readOnly('car_brand'),
            ...$this->readOnly('car_model'),
            ...$this->readOnly('part'),
            ...$this->readOnly('time_slot'),
            'access_admin_panel',
        ]);

        // ─── foreman: Старший мастер ────────────────────────────────────────
        // Координатор производственной зоны. Получает сформированные заказ-наряды,
        // назначает мастера-исполнителя (assign_order_executor), контролирует ход
        // работ и утверждает закрытие заказ-наряда (change_order_status).
        $foreman = Role::firstOrCreate(['name' => 'foreman', 'guard_name' => 'web']);
        $foreman->syncPermissions([
            'view_any_order', 'view_order', 'update_order',
            'assign_order_executor',
            'change_order_status',
            ...$this->readOnly('appointment'),
            ...$this->readOnly('client'),
            ...$this->readOnly('car'),
            ...$this->readOnly('part'),
            ...$this->readOnly('service'),
            ...$this->readOnly('category'),
            'access_admin_panel',
        ]);

        // ─── warehouseman: Кладовщик ────────────────────────────────────────
        // Только склад: ведёт запчасти, выдаёт/принимает, видит заказы.
        $warehouseman = Role::firstOrCreate(['name' => 'warehouseman', 'guard_name' => 'web']);
        $warehouseman->syncPermissions([
            'view_any_part', 'view_part', 'create_part', 'update_part',
            'view_any_order', 'view_order',
            // Заявки механиков на запчасти: видит и обрабатывает (выдаёт/отклоняет)
            'view_any_part_request', 'view_part_request', 'update_part_request',
            'issue_part',
            'receive_part',
            'view_warehouse_reports',
            'access_admin_panel',
        ]);

        // ─── mechanic: Автомеханик ──────────────────────────────────────────
        // Видит только свои заказы (фильтр в OrderResource::getEloquentQuery),
        // меняет статус закреплённых за ним услуг.
        $mechanic = Role::firstOrCreate(['name' => 'mechanic', 'guard_name' => 'web']);
        $mechanic->syncPermissions([
            'view_any_order', 'view_order',
            ...$this->readOnly('car'),
            ...$this->readOnly('part'),
            ...$this->readOnly('service'),
            ...$this->readOnly('category'),
            // Запрос запчастей у кладовщика: создаёт и видит свои заявки
            'view_any_part_request', 'view_part_request', 'create_part_request',
            'change_own_service_status',
            'access_admin_panel',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function crudNoDelete(string $resource): array
    {
        return [
            "view_any_{$resource}",
            "view_{$resource}",
            "create_{$resource}",
            "update_{$resource}",
        ];
    }

    /**
     * @return array<int, string>
     */
    private function readOnly(string $resource): array
    {
        return [
            "view_any_{$resource}",
            "view_{$resource}",
        ];
    }

    private function createDefaultAdmin(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'mihail@mail.ru'],
            [
                'name' => 'Администратор',
                'login' => 'admin',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );

        // На случай, если учётка уже существовала до появления поля login.
        if (blank($user->login)) {
            $user->forceFill(['login' => 'admin'])->save();
        }

        $user->syncRoles(['super_admin']);
    }
}
