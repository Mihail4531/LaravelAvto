<?php

namespace App\Filament\Resources\AccessControl\RoleResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\Pages\ViewRole as BaseViewRole;
use App\Filament\Resources\AccessControl\RoleResource;

class ViewRole extends BaseViewRole
{
    protected static string $resource = RoleResource::class;
}
