<?php

namespace App\Filament\Resources\AccessControl\PermissionResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource\Pages\ListPermissions as BaseListPermissions;
use App\Filament\Resources\AccessControl\PermissionResource;

class ListPermissions extends BaseListPermissions
{
    protected static string $resource = PermissionResource::class;
}
