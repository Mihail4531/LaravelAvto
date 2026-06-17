<?php

namespace App\Filament\Resources\AccessControl\RoleResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\Pages\EditRole as BaseEditRole;
use App\Filament\Resources\AccessControl\RoleResource;
use Filament\Actions\Action;

class EditRole extends BaseEditRole
{
    protected static string $resource = RoleResource::class;

    /**
     * Если управляющий редактирует роль, которую сам носит, требуем
     * подтверждение перед сохранением — страховка, чтобы он не снял у себя
     * доступ к панели и не заблокировал сам себя одним кликом.
     */
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->requiresConfirmation(fn (): bool => $this->editingOwnRole())
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalHeading('Вы редактируете собственную роль')
            ->modalDescription('Если вы убрали у этой роли доступ к панели или ключевые права, после сохранения вы можете потерять доступ к системе. Проверьте изменения перед сохранением.')
            ->modalSubmitActionLabel('Да, сохранить')
            ->modalCancelActionLabel('Отмена');
    }

    private function editingOwnRole(): bool
    {
        $user = auth()->user();

        return $user !== null
            && ! $user->hasRole('super_admin')
            && $user->hasRole($this->record->name);
    }
}
