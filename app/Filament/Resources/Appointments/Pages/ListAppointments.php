<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Appointment;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Создать заявку'), // но обычно не используется
        ];
    }

    public function getTabs(): array
    {
        return [
            'new' => Tab::make('Новые')
                ->badge(fn () => Appointment::where('status', 'new')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new')),
            'confirmed' => Tab::make('Подтверждённые')
                ->badge(fn () => Appointment::where('status', 'confirmed')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')),
            'converted' => Tab::make('Преобразованные')
                ->badge(fn () => Appointment::where('status', 'converted')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'converted')),
            'rejected' => Tab::make('Отклонённые')
                ->badge(fn () => Appointment::where('status', 'rejected')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'cancelled' => Tab::make('Отменённые')
                ->badge(fn () => Appointment::where('status', 'cancelled')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
