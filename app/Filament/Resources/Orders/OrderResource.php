<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\CustomerPartsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\PartsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Filament\Traits\ResourcePermissions;
use App\Models\Order;
use App\Models\User;
use App\Support\BranchScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OrderResource extends Resource
{
    use ResourcePermissions;

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Заказ-наряды';

    protected static ?string $modelLabel = 'заказ-наряд';

    protected static ?string $pluralModelLabel = 'Заказ-наряды';

    protected static string|UnitEnum|null $navigationGroup = 'Работа';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationBadge(): ?string
    {
        $count = Order::whereIn('status', ['new', 'in_progress'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdue = Order::whereIn('status', ['new', 'in_progress'])
            ->whereNotNull('planned_finish')
            ->where('planned_finish', '<', now())
            ->count();

        return $overdue > 0 ? 'danger' : 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ServicesRelationManager::class,
            PartsRelationManager::class,
            CustomerPartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Текущий пользователь — механик, которому видны только свои наряды
     * (не директор и не админ).
     */
    public static function isLimitedToOwn(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasRole('mechanic')
            && ! $user->hasAnyRole(['super_admin', 'director']);
    }

    public static function getNavigationLabel(): string
    {
        return static::isLimitedToOwn() ? 'Мои наряды' : 'Заказ-наряды';
    }

    // Механик видит только заказы со своими услугами.
    // Остальные роли — все заказы (фильтр через permission на уровне trait).
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withTrashed();

        if (static::isLimitedToOwn()) {
            $query->whereHas('services', fn (Builder $q) => $q->where('executor_id', auth()->id()));
        }

        // Разграничение по филиалам: сотрудник видит заказы только своего филиала,
        // управляющий/админ — всю сеть (см. App\Support\BranchScope).
        BranchScope::apply($query);

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
