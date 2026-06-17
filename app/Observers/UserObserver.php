<?php

namespace App\Observers;

use App\Models\Position;
use App\Models\User;

/**
 * Синхронизирует роль пользователя с его должностью.
 *
 * Правило: роль определяется через Position.default_role.
 * При смене должности роль автоматически перевыдаётся.
 *
 * Технические роли (super_admin), не привязанные к должности,
 * сохраняются — синхронизация трогает только роли из справочника должностей.
 */
class UserObserver
{
    public function created(User $user): void
    {
        $this->syncRoleFromPosition($user);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('position_id')) {
            $this->syncRoleFromPosition($user);
        }
    }

    private function syncRoleFromPosition(User $user): void
    {
        // Список всех ролей, которые управляются через должности.
        // Эти роли мы можем безопасно снимать/добавлять при смене Position.
        $managedRoles = Position::query()
            ->whereNotNull('default_role')
            ->pluck('default_role')
            ->unique()
            ->all();

        // Сохраняем «технические» роли пользователя, которые не привязаны к должностям
        // (например, super_admin для ИТ-сопровождения).
        $preservedRoles = $user->roles()
            ->whereNotIn('name', $managedRoles)
            ->pluck('name')
            ->all();

        $newRoles = $preservedRoles;

        $position = $user->position_id ? Position::find($user->position_id) : null;
        if ($position && $position->default_role) {
            $newRoles[] = $position->default_role;
        }

        $user->syncRoles(array_values(array_unique($newRoles)));
    }
}
