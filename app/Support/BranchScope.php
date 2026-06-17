<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Разграничение операционных данных по филиалам (мультифилиальность).
 *
 * Модель бизнеса — ОДНА компания с несколькими точками, поэтому это не
 * жёсткая изоляция арендаторов, а «рабочая область сотрудника»:
 *   • Управляющий (director) и super_admin видят всю сеть целиком — у них
 *     есть право view_all_branches (super_admin к тому же проходит через
 *     Gate::before в AppServiceProvider).
 *   • Приёмщик, старший мастер, кладовщик и механик видят только данные
 *     СВОЕГО филиала (по User.branch_id).
 *
 * Безопасно при одном филиале: пока филиал один, фильтр ни на что не влияет,
 * а UI выбора/колонок филиала скрыт (см. shouldShowBranchUi). Появится вторая
 * точка — разграничение и интерфейс включатся сами, без доработок кода.
 *
 * Намеренно НЕ разграничиваются (общие для всей сети):
 *   • справочники — услуги, категории, авто-справочник, должности, роли;
 *   • клиенты и их автомобили — клиент может приехать на любую точку;
 *   • сотрудники (раздел «Сотрудники») — это HR-зона управляющего, не операционка.
 *
 * Сотрудник без филиала (branch_id = null) НЕ ограничивается: пока его не
 * закрепили за точкой, он «плавающий» и видит всё. Это удобно для настройки
 * и не ломает демонстрацию при одном филиале.
 */
class BranchScope
{
    /**
     * Видит ли пользователь данные всех филиалов (управляющий / ИТ-админ).
     */
    public static function seesAllBranches(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->can('view_all_branches');
    }

    /**
     * Филиал текущего сотрудника (null — не закреплён за точкой).
     */
    public static function currentBranchId(): ?int
    {
        $user = auth()->user();

        return $user instanceof User ? $user->branch_id : null;
    }

    /**
     * Нужно ли реально ограничивать выборку текущему пользователю.
     * Ограничиваем только закреплённых за филиалом, кто не видит всю сеть.
     */
    public static function isRestricted(): bool
    {
        return ! static::seesAllBranches() && static::currentBranchId() !== null;
    }

    /**
     * Ограничить запрос филиалом сотрудника по прямой колонке (branch_id).
     */
    public static function apply(Builder $query, string $column = 'branch_id'): Builder
    {
        if (! static::isRestricted()) {
            return $query;
        }

        $qualified = $query->getModel()->getTable().'.'.$column;

        return $query->where($qualified, static::currentBranchId());
    }

    /**
     * Ограничить запрос через связь, у которой есть branch_id
     * (например, заявка на запчасть → заказ → филиал).
     */
    public static function applyViaRelation(Builder $query, string $relation, string $column = 'branch_id'): Builder
    {
        if (! static::isRestricted()) {
            return $query;
        }

        return $query->whereHas(
            $relation,
            fn (Builder $q) => $q->where($column, static::currentBranchId())
        );
    }

    /**
     * Филиал по умолчанию для форм — подставляем филиал сотрудника.
     */
    public static function defaultBranchId(): ?int
    {
        return static::currentBranchId();
    }

    /**
     * Показывать ли элементы UI, связанные с филиалом (колонка/фильтр «Филиал»):
     * только когда сеть видна целиком и филиалов реально больше одного. Пока
     * филиал один — лишний интерфейс не мозолит глаза.
     */
    public static function shouldShowBranchUi(): bool
    {
        return static::seesAllBranches() && static::hasMultipleBranches();
    }

    /**
     * В системе больше одного филиала. Кэшируется на время запроса.
     */
    public static function hasMultipleBranches(): bool
    {
        static $multiple = null;

        return $multiple ??= Branch::count() > 1;
    }
}
