<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\Part;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use UnitEnum;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $navigationLabel = 'Отчёты';

    protected static ?string $title = 'Отчёты и выгрузки';

    protected static string|UnitEnum|null $navigationGroup = 'Отчёты';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.reports';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        $user = request()->user();

        return $user instanceof User
            && $user->can('view_financial_reports');
    }

    /**
     * Сводная статистика для шапки страницы
     */
    public function getStats(): array
    {
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : now()->startOfMonth();
        $to = $this->dateTo ? Carbon::parse($this->dateTo)->endOfDay() : now()->endOfDay();

        return [
            'orders' => Order::whereBetween('created_at', [$from, $to])->count(),
            'income' => (float) Payment::whereBetween('created_at', [$from, $to])->sum('amount'),
            'appointments' => Appointment::whereBetween('created_at', [$from, $to])->count(),
            'parts_total' => Part::count(),
            'parts_low' => Part::whereRaw('min_stock_quantity > 0 AND (stock_quantity - reserved_quantity) <= min_stock_quantity')->count(),
        ];
    }
}
