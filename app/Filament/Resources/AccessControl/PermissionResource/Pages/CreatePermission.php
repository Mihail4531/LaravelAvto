<?php

namespace App\Filament\Resources\AccessControl\PermissionResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource\Pages\CreatePermission as BaseCreatePermission;
use App\Filament\Resources\AccessControl\PermissionResource;

class CreatePermission extends BaseCreatePermission
{
    protected static string $resource = PermissionResource::class;
}
