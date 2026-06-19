<?php

namespace App\Filament\Resources\PartRequests\Tables;

use App\Models\Branch;
use App\Models\PartRequest;
use App\Support\BranchScope;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->formatStateUsing(fn ($state) => 'Заказ №'.$state)
                    ->sortable(),

                TextColumn::make('order.branch.name')
                    ->label('Филиал')
                    ->placeholder('—')
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),

                TextColumn::make('part.name')
                    ->label('Запчасть')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->numeric(),

                TextColumn::make('mechanic.name')
                    ->label('Взял (ФИО)')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('mechanic.position.name')
                    ->label('Должность')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PartRequest::statuses()[$state] ?? $state)
                    ->color(fn ($state) => PartRequest::statusColor($state))
                    ->icon(fn ($state) => PartRequest::statusIcon($state)),

                TextColumn::make('created_at')
                    ->label('Когда')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('branch')
                    ->label('Филиал')
                    ->options(fn () => Branch::orderBy('name')->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->whereHas('order', fn (Builder $q) => $q->where('branch_id', $data['value']))
                        : $query)
                    ->visible(fn () => BranchScope::shouldShowBranchUi()),
            ])
            // Журнал выдач — это лог. Чистить его (когда записей много) может тот,
            // у кого есть право на удаление (управляющий, super_admin). Удаление
            // записи журнала склад НЕ меняет — остаток уже изменён движением.
            ->recordActions([
                DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn () => auth()->user()?->can('delete_part_request')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->visible(fn () => auth()->user()?->can('delete_part_request')),
                ]),
            ]);
    }
}
