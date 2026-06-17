<?php

namespace App\Filament\Resources\AccessControl\PermissionResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource\Pages\ViewPermission as BaseViewPermission;
use App\Filament\Resources\AccessControl\PermissionResource;

class ViewPermission extends BaseViewPermission
{
    protected static string $resource = PermissionResource::class;
}
