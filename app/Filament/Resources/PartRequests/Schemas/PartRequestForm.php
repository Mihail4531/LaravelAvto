<?php

namespace App\Filament\Resources\PartRequests\Schemas;

use App\Models\Order;
use App\Models\Part;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PartRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Заказ-наряд')
                    ->options(function () {
                        $user = auth()->user();

                        $query = Order::query()
                            ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_IN_PROGRESS])
                            ->with('car');

                        // Механик — только свои заказы; остальные (управляющий и т.п.) — все активные.
                        if ($user && $user->hasRole('mechanic') && ! $user->hasAnyRole(['super_admin', 'director', 'warehouseman'])) {
                            $query->whereHas('services', fn ($q) => $q->where('executor_id', $user->id));
                        }

                        return $query->get()->mapWithKeys(fn (Order $o) => [
                            $o->id => 'Заказ №'.$o->id.($o->car ? ' — '.$o->car->display_name : ''),
                        ]);
                    })
                    ->required()
                    ->searchable()
                    ->helperText('Активные заказы (новые и в работе).'),

                Select::make('part_id')
                    ->label('Запчасть / материал')
                    ->options(function () {
                        return Part::where('active', true)
                            ->with('carModels.brand')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Part $p) => [
                                $p->id => $p->name.' ['.$p->applicabilityLabel().'] · доступно '.$p->available_quantity.' '.$p->unit,
                            ]);
                    })
                    ->required()
                    ->searchable()
                    ->live(),

                TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live(debounce: 400),

                Placeholder::make('stock_note')
                    ->label('Наличие')
                    ->content(fn (callable $get) => static::stockNote($get('part_id'), $get('quantity'))),

                Textarea::make('comment')
                    ->label('Комментарий для кладовщика')
                    ->rows(2)
                    ->nullable()
                    ->placeholder('Например: нужно срочно, авто на подъёмнике'),
            ]);
    }

    /**
     * Мягкая подсказка по наличию при заявке (не блокирует — заявка может
     * превышать остаток как сигнал кладовщику дозаказать). Жёсткая проверка —
     * при выдаче в PartRequest::fulfill().
     */
    public static function stockNote($partId, $quantity): string|HtmlString
    {
        if (! $partId) {
            return '';
        }

        $part = Part::find($partId);
        if (! $part) {
            return '';
        }

        $avail = (float) $part->available_quantity;
        $qty = (float) ($quantity ?: 0);

        if ($qty > $avail) {
            return new HtmlString('<span style="color:#F59E0B;font-size:13px;font-weight:600;">⚠ Запрошено '.$qty.', а свободно '.$avail.' '.$part->unit.'. Заявку можно создать, но кладовщику нужно будет дозаказать.</span>');
        }

        return new HtmlString('<span style="color:#10B981;font-size:13px;font-weight:600;">✓ В наличии достаточно (свободно '.$avail.' '.$part->unit.')</span>');
    }
}
