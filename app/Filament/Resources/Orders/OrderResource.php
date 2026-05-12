<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // Иконка в меню (строка для v5)
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    // Название в меню
    protected static ?string $navigationLabel = 'Заказы';

    // Единственное число
    protected static ?string $modelLabel = 'заказ';

    // Множественное число
    protected static ?string $pluralModelLabel = 'Заказы';

    // Группа в боковом меню
    protected static string|UnitEnum|null $navigationGroup = 'Клиенты и заказы';

    // Порядок сортировки внутри группы (клиенты – 1, заявки – 2, заказы – 3)
   protected static ?int $navigationSort = 4;

    // Поле для отображения заголовка записи (в хлебных крошках)
    protected static ?string $recordTitleAttribute = 'id';

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    // Показывать в том числе мягко удалённые записи (для вкладок)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    // Для корректной загрузки удалённой записи при редактировании по прямому URL
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
