<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Components\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum; // ← добавить для корректного типа

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    // Иконка для пункта меню
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    // Название пункта в меню (строка)
    protected static ?string $navigationLabel = 'Филиалы';

    // Единственное число (для заголовков)
    protected static ?string $modelLabel = 'филиал';

    // Множественное число (для заголовков)
    protected static ?string $pluralModelLabel = 'Филиалы';

    // Группа в боковом меню – тип должен совпадать с родительским (string|UnitEnum|null)
    protected static string|UnitEnum|null $navigationGroup = 'Филиалы';
protected static ?int $navigationSort = 1;

    // Поле для заголовка записи
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }
    public static function getRelations(): array
    {
        return [];
    }
 public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
