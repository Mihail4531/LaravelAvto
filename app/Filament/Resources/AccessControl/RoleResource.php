<?php

namespace App\Filament\Resources\AccessControl;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource as BaseRoleResource;
use App\Filament\Resources\AccessControl\RoleResource\Pages\CreateRole;
use App\Filament\Resources\AccessControl\RoleResource\Pages\EditRole;
use App\Filament\Resources\AccessControl\RoleResource\Pages\ListRoles;
use App\Filament\Resources\AccessControl\RoleResource\Pages\ViewRole;
use App\Support\AccessLabels;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Permission;

class RoleResource extends BaseRoleResource
{
    protected static ?string $slug = 'roles';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    /**
     * Доступ к разделу «Роли» — по правам (view_any_role, create_role, …),
     * которые управляющий получает через админку. Аварийную роль super_admin
     * редактировать/удалять может только сам super_admin — см. isEditableRole().
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_role') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('view_role') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_role') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (auth()->user()?->can('update_role') ?? false)
            && static::isEditableRole($record);
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->can('delete_role') ?? false)
            && static::isEditableRole($record)
            && ! static::userHoldsRole($record);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_role') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_role') ?? false;
    }

    /**
     * Роль super_admin — «ключ восстановления» (break-glass). Менять и удалять
     * её может только сам super_admin, чтобы управляющий не мог случайно
     * лишить систему аварийного доступа.
     */
    private static function isEditableRole(Model $record): bool
    {
        if ($record->name === 'super_admin') {
            return auth()->user()?->hasRole('super_admin') ?? false;
        }

        return true;
    }

    /**
     * Носит ли текущий пользователь эту роль. Удалять собственную роль нельзя
     * (мгновенная блокировка без возможности предупредить) — это делается из
     * другого аккаунта или после снятия роли с себя.
     */
    private static function userHoldsRole(Model $record): bool
    {
        return auth()->user()?->hasRole($record->name) ?? false;
    }

    /**
     * Правда, если текущий пользователь редактирует роль, которую сам носит,
     * и среди выбранных прав больше нет access_admin_panel — значит после
     * сохранения он потеряет доступ к панели и заблокирует сам себя.
     */
    private static function wouldLockOutSelf(mixed $selectedPermissions, ?Model $record): bool
    {
        $user = auth()->user();

        if (! $user || ! $record || $user->hasRole('super_admin')) {
            return false;
        }

        if (! $user->hasRole($record->name)) {
            return false;
        }

        $accessPanelId = Permission::query()->where('name', 'access_admin_panel')->value('id');

        if (! $accessPanelId) {
            return false;
        }

        $selected = collect($selectedPermissions ?? [])->map(fn ($id): int => (int) $id);

        return ! $selected->contains((int) $accessPanelId);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.name'))
                    ->formatStateUsing(fn (string $state) => AccessLabels::role($state))
                    ->searchable(),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.permissions_count')),
                TextColumn::make('guard_name')
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.guard_name'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ]);
    }

    public static function getPages(): array
    {
        if (config('filament-spatie-roles-permissions.should_use_simple_modal_resource.roles')) {
            return [
                'index' => ListRoles::route('/'),
            ];
        }

        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
            'view' => ViewRole::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.name'))
                        ->required()
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule),

                    Select::make('guard_name')
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.guard_name'))
                        ->options(config('filament-spatie-roles-permissions.guard_names'))
                        ->default(config('filament-spatie-roles-permissions.default_guard_name'))
                        ->visible(fn () => config('filament-spatie-roles-permissions.should_show_guard', true))
                        ->required(),

                    Select::make('permissions')
                        ->columnSpanFull()
                        ->multiple()
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.permissions'))
                        ->relationship(
                            name: 'permissions',
                            modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => AccessLabels::permission($record->name))
                        ->searchable(['name'])
                        ->live()
                        ->preload((bool) config('filament-spatie-roles-permissions.preload_permissions', true)),

                    // Живое предупреждение: подсвечивается, как только управляющий
                    // снимает у СВОЕЙ роли доступ к панели (access_admin_panel).
                    Placeholder::make('self_lockout_warning')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->visible(fn (Get $get, ?Model $record): bool => static::wouldLockOutSelf($get('permissions'), $record))
                        ->content(new HtmlString(
                            '<div style="border:1px solid #ef4444;background:rgba(239,68,68,.08);'
                            .'color:#ef4444;padding:.6rem .8rem;border-radius:6px;font-size:13px;font-weight:600;line-height:1.4;">'
                            .'⚠ Вы снимаете у своей роли доступ к панели управления. '
                            .'Если сохранить так — вы потеряете доступ к админ-панели. Не снимайте это право у себя.'
                            .'</div>'
                        )),
                ]),
            ]),
        ]);
    }
}
