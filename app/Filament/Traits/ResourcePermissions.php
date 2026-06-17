<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Авторизация Filament Resource через Spatie permissions.
 *
 * Ключ permission генерируется из имени модели: App\Models\Order → 'order',
 * App\Models\CarBrand → 'car_brand'. Проверяемые permissions:
 *   view_any_<key>, view_<key>, create_<key>, update_<key>, delete_<key>.
 *
 * Super_admin бесконтрольно проходит через Gate::before (см. AppServiceProvider).
 */
trait ResourcePermissions
{
    protected static function permissionKey(): string
    {
        return Str::snake(class_basename(static::$model));
    }

    protected static function userCan(string $ability): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can($ability);
    }

    public static function canViewAny(): bool
    {
        return static::userCan('view_any_'.static::permissionKey());
    }

    public static function canView(Model $record): bool
    {
        return static::userCan('view_'.static::permissionKey());
    }

    public static function canCreate(): bool
    {
        return static::userCan('create_'.static::permissionKey());
    }

    public static function canEdit(Model $record): bool
    {
        return static::userCan('update_'.static::permissionKey());
    }

    public static function canDelete(Model $record): bool
    {
        return static::userCan('delete_'.static::permissionKey());
    }

    public static function canDeleteAny(): bool
    {
        return static::userCan('delete_'.static::permissionKey());
    }

    public static function canRestore(Model $record): bool
    {
        return static::userCan('delete_'.static::permissionKey());
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::userCan('delete_'.static::permissionKey());
    }
}
