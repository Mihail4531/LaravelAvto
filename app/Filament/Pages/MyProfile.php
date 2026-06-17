<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;

class MyProfile extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Мой профиль';

    protected static ?string $title = 'Мой профиль';

    // В сайдбаре не показываем — открывается из меню пользователя (справа вверху).
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.my-profile';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Редактировать профиль')
                ->icon('heroicon-o-pencil-square')
                ->url(filament()->getProfileUrl()),
        ];
    }

    public function getUser(): User
    {
        return auth()->user();
    }
}
