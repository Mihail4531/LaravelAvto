<?php

namespace App\Filament\Resources\PartRequests\Tables;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Branch;
use App\Models\PartRequest;
use App\Support\BranchScope;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
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
                    ->label('Запросил')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PartRequest::statuses()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        PartRequest::STATUS_PENDING => 'warning',
                        PartRequest::STATUS_ISSUED => 'success',
                        PartRequest::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('issued_at')
                    ->label('Выдана')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(PartRequest::statuses()),
            ])
            ->recordActions([
                Action::make('issue')
                    ->label('Выдать')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PartRequest $record) => $record->status === PartRequest::STATUS_PENDING
                        && auth()->user()?->can('issue_part'))
                    ->requiresConfirmation()
                    ->modalHeading('Выдача запчасти по заявке')
                    ->modalDescription('Деталь будет списана со склада и добавлена в заказ-наряд как выданная.')
                    ->action(function (PartRequest $record) {
                        $part = $record->part;
                        $need = (float) $record->quantity;
                        $avail = (float) ($part?->available_quantity ?? 0);

                        if (! $part) {
                            Notification::make()->title('Запчасть не найдена')->danger()->send();

                            return;
                        }

                        // Нехватка — не выдаём, а подсказываем оприходовать/дозаказать
                        if ($need > $avail) {
                            $short = $need - $avail;

                            Notification::make()
                                ->title('Недостаточно на складе')
                                ->body("Нужно {$need} {$part->unit}, свободно {$avail}. Не хватает {$short} {$part->unit} — оприходуйте поступление или дозакажите.")
                                ->warning()
                                ->persistent()
                                ->actions([
                                    NotificationAction::make('openPart')
                                        ->label('Открыть карточку запчасти')
                                        ->url(PartResource::getUrl('edit', ['record' => $part->id]))
                                        ->button(),
                                ])
                                ->send();

                            return;
                        }

                        try {
                            $record->fulfill(auth()->id());

                            Notification::make()
                                ->title('Запчасть выдана и добавлена в заказ №'.$record->order_id)
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Не удалось выдать')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PartRequest $record) => $record->status === PartRequest::STATUS_PENDING
                        && auth()->user()?->can('update_part_request'))
                    ->schema([
                        Textarea::make('comment')
                            ->label('Причина отклонения')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (PartRequest $record, array $data) {
                        $record->update([
                            'status' => PartRequest::STATUS_REJECTED,
                            'comment' => $data['comment'] ?? $record->comment,
                        ]);

                        Notification::make()
                            ->title('Заявка отклонена')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
