<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateTimeSlots extends Command
{
    protected $signature = 'slots:generate
                            {--days=30 : Сколько дней вперёд генерировать}
                            {--interval=60 : Интервал слота в минутах}
                            {--branch= : ID конкретного филиала (по умолчанию — все)}';

    protected $description = 'Генерирует временные слоты для записи на ближайшие N дней';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $interval = (int) $this->option('interval');
        $branchId = $this->option('branch');

        $branches = $branchId
            ? Branch::where('id', $branchId)->where('active', true)->get()
            : Branch::where('active', true)->get();

        if ($branches->isEmpty()) {
            $this->error('Активных филиалов не найдено.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach ($branches as $branch) {
            $startHour = $branch->work_time_start
                ? (int) $branch->work_time_start->format('H')
                : 9;
            $endHour = $branch->work_time_end
                ? (int) $branch->work_time_end->format('H')
                : 18;

            for ($d = 0; $d < $days; $d++) {
                $day = Carbon::now()->startOfDay()->addDays($d);

                // Пропускаем нерабочие дни
                if (! $this->isWorkingDay($branch, $day)) {
                    continue;
                }

                $current = $day->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
                $end = $day->copy()->setHour($endHour)->setMinute(0)->setSecond(0);

                while ($current->lt($end)) {
                    $slotEnd = $current->copy()->addMinutes($interval);

                    $exists = TimeSlot::where('branch_id', $branch->id)
                        ->where('starts_at', $current)
                        ->exists();

                    if (! $exists) {
                        TimeSlot::create([
                            'branch_id' => $branch->id,
                            'starts_at' => $current->toDateTimeString(),
                            'ends_at' => $slotEnd->toDateTimeString(),
                            'available' => true,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }

                    $current->addMinutes($interval);
                }
            }
        }

        $this->info("Готово. Создано: {$created}, пропущено (уже существуют): {$skipped}");

        return self::SUCCESS;
    }

    private function isWorkingDay(Branch $branch, Carbon $day): bool
    {
        $dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayName = $dayNames[$day->dayOfWeekIso - 1];

        if (! $branch->work_days_start || ! $branch->work_days_end) {
            // Если расписание не задано — работаем пн-пт
            return $day->isWeekday();
        }

        $start = array_search($branch->work_days_start, $dayNames);
        $end = array_search($branch->work_days_end, $dayNames);
        $cur = array_search($dayName, $dayNames);

        if ($start === false || $end === false) {
            return $day->isWeekday();
        }

        return $cur >= $start && $cur <= $end;
    }
}
