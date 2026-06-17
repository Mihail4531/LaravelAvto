<?php

namespace App\Filament\Resources\AccessControl\PermissionResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource\Pages\EditPermission as BaseEditPermission;
use App\Filament\Resources\AccessControl\PermissionResource;

class EditPermission extends BaseEditPermission
{
    protected static string $resource = PermissionResource::class;
}
