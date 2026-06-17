<?php

namespace App\Filament\Resources\AccessControl;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource as BasePermissionResource;
use App\Filament\Resources\AccessControl\PermissionResource\Pages\CreatePermission;
use App\Filament\Resources\AccessControl\PermissionResource\Pages\EditPermission;
use App\Filament\Resources\AccessControl\PermissionResource\Pages\ListPermissions;
use App\Filament\Resources\AccessControl\PermissionResource\Pages\ViewPermission;
use App\Models\User;
use App\Support\AccessLabels;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PermissionResource extends BasePermissionResource
{
    protected static ?string $slug = 'permissions';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    /**
     * Раздел «Разрешения» доступен только ИТ-администратору (super_admin).
     */
    private static function isSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function canViewAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canView(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isSuperAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.name'))
                    ->formatStateUsing(fn (string $state) => AccessLabels::permission($state))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.guard_name'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('guard_name')
                    ->label(__('filament-spatie-roles-permissions::filament-spatie.field.guard_name'))
                    ->multiple()
                    ->options(config('filament-spatie-roles-permissions.guard_names')),
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        if (config('filament-spatie-roles-permissions.should_use_simple_modal_resource.permissions')) {
            return [
                'index' => ListPermissions::route('/'),
            ];
        }

        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.name'))
                        ->required(),
                    Select::make('guard_name')
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.guard_name'))
                        ->options(config('filament-spatie-roles-permissions.guard_names'))
                        ->default(config('filament-spatie-roles-permissions.default_guard_name'))
                        ->visible(fn () => config('filament-spatie-roles-permissions.should_show_guard', true))
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('roles', null))
                        ->required(),
                    Select::make('roles')
                        ->multiple()
                        ->label(__('filament-spatie-roles-permissions::filament-spatie.field.roles'))
                        ->relationship(
                            name: 'roles',
                            modifyQueryUsing: function ($query, Get $get) {
                                if (! empty($get('guard_name'))) {
                                    $query->where('guard_name', $get('guard_name'));
                                }

                                return $query;
                            },
                        )
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => AccessLabels::role($record->name))
                        ->preload(true),
                ]),
            ]),
        ]);
    }
}
