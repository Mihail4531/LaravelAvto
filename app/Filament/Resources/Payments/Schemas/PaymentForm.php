<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                TextInput::make('cashier_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('paid_at')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('method')
                    ->required(),
                TextInput::make('comment'),
            ]);
    }
}
