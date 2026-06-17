<?php

namespace App\Filament\Resources\PartRequests;

use App\Filament\Resources\PartRequests\Pages\CreatePartRequest;
use App\Filament\Resources\PartRequests\Pages\ListPartRequests;
use App\Filament\Resources\PartRequests\Schemas\PartRequestForm;
use App\Filament\Resources\PartRequests\Tables\PartRequestsTable;
use App\Filament\Traits\ResourcePermissions;
use App\Models\PartRequest;
use App\Models\User;
use App\Support\BranchScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PartRequestResource extends Resource
{
    use ResourcePermissions;

    protected static ?string $model = PartRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Запросы со склада';

    protected static ?string $modelLabel = 'заявку на запчасть';

    protected static ?string $pluralModelLabel = 'Заявки на запчасти';

    protected static string|UnitEnum|null $navigationGroup = 'Склад';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', PartRequest::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return PartRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Механик видит только свои заявки. Кладовщик/директор/админ — все.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user instanceof User
            && $user->hasRole('mechanic')
            && ! $user->hasAnyRole(['super_admin', 'director', 'warehouseman'])
        ) {
            $query->where('mechanic_id', $user->id);
        }

        // Разграничение по филиалам: заявка относится к филиалу своего заказа.
        // Кладовщик/мастер видят только заявки своей точки (см. BranchScope).
        BranchScope::applyViaRelation($query, 'order');

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartRequests::route('/'),
            'create' => CreatePartRequest::route('/create'),
        ];
    }
}
