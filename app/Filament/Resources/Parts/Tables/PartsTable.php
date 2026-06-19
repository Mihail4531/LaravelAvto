<?php

namespace App\Filament\Resources\Parts\Tables;

use App\Models\Part;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Ед. изм.')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена (₽)')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('На складе')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('available_quantity')
                    ->label('Свободно')
                    ->numeric()
                    ->sortable(false)
                    ->color(fn ($record) => match (true) {
                        $record->available_quantity <= 0 => 'danger',
                        $record->min_stock_quantity > 0 && $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn ($record) => $record->isLowStock()
                        ? "Мало: минимум {$record->min_stock_quantity} {$record->unit}"
                        : null
                    ),

                TextColumn::make('applicability')
                    ->label('Применяемость')
                    ->state(fn ($record) => $record->applicabilityLabel())
                    ->badge()
                    ->color(fn ($record) => $record->is_universal ? 'info' : ($record->carModels->isNotEmpty() ? 'success' : 'gray'))
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('location')
                    ->label('Место хранения')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('active')
                    ->label('Активна')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Добавлена')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            // Дефицитные позиции — акцентная полоса слева (CSS .ais-row-low)
            ->recordClasses(fn (Part $record): ?string => $record->min_stock_quantity > 0 && $record->isLowStock()
                ? 'ais-row-low'
                : null)
            ->filters([
                TernaryFilter::make('active')
                    ->label('Активные')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('reports.parts'))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make()->label('Редактировать')
                    ->visible(fn () => auth()->user()?->can('update_part')),

                DeleteAction::make()
                    ->label('Удалить')
                    ->visible(fn () => auth()->user()?->can('delete_part'))
                    // Нельзя удалить запчасть с историей — блокируем с подсказкой.
                    ->before(function (Part $record, DeleteAction $action) {
                        if (! $record->isDeletable()) {
                            Notification::make()
                                ->title('Нельзя удалить запчасть')
                                ->body('«'.$record->name.'» уже использовалась в заказах или есть движения по складу. Снимите флажок «Активна», чтобы убрать её из выбора, — история при этом сохранится.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        // Если среди выбранных есть «используемые» — отменяем удаление
                        // целиком с перечнем, чтобы не оставить полудело.
                        ->before(function (Collection $records, DeleteBulkAction $action) {
                            $blocked = $records->filter(fn (Part $p) => ! $p->isDeletable());

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title('Часть запчастей удалить нельзя')
                                    ->body('Используются в заказах или движениях склада: '.$blocked->pluck('name')->implode(', ').'. Сделайте их неактивными вместо удаления.')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ])->visible(fn () => auth()->user()?->can('delete_part')),
            ]);
    }
}
