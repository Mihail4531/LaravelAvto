<?php

namespace App\Filament\Resources\AccessControl\RoleResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\Pages\ListRoles as BaseListRoles;
use App\Filament\Resources\AccessControl\RoleResource;

class ListRoles extends BaseListRoles
{
    protected static string $resource = RoleResource::class;
}
