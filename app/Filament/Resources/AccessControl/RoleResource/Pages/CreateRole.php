<?php

namespace App\Filament\Resources\AccessControl\RoleResource\Pages;

use Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource\Pages\CreateRole as BaseCreateRole;
use App\Filament\Resources\AccessControl\RoleResource;

class CreateRole extends BaseCreateRole
{
    protected static string $resource = RoleResource::class;
}
