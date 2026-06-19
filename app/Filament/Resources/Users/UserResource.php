<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Traits\HiddenFromSidebarNav;
use App\Filament\Traits\ResourcePermissions;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
    use HiddenFromSidebarNav;
    use ResourcePermissions;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Сотрудники';

    protected static ?string $modelLabel = 'сотрудника';

    protected static ?string $pluralModelLabel = 'Сотрудники';

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Себя редактируем ТОЛЬКО через «Мой профиль» — там подтверждение текущего
     * пароля и контролируемая смена пароля. Через «Сотрудники» правка своей
     * записи запрещена, иначе можно сменить себе пароль/роль/активность в обход
     * этих проверок. Скрывает кнопку «Редактировать» у своей строки и блокирует
     * прямой заход на /users/{id}/edit для самого себя.
     */
    public static function canEdit(Model $record): bool
    {
        if ($record->getKey() === auth()->id()) {
            return false;
        }

        return auth()->user()?->can('update_user') ?? false;
    }

    /**
     * Запрещаем сотруднику удалять собственную учётную запись —
     * иначе можно случайно лишить себя доступа к панели.
     */
    public static function canDelete(Model $record): bool
    {
        if ($record->getKey() === auth()->id()) {
            return false;
        }

        return auth()->user()?->can('delete_user') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
