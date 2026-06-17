<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class PositionsSeeder extends Seeder
{
    /**
     * Упрощённое штатное расписание.
     * default_role = NULL → должность без доступа в АИС.
     */
    private const POSITIONS = [
        ['name' => 'Управляющий',                'hourly_rate' => 1000, 'default_role' => 'director'],
        ['name' => 'Приёмщик',                   'hourly_rate' => 550, 'default_role' => 'receptionist'],
        ['name' => 'Старший мастер',             'hourly_rate' => 700, 'default_role' => 'foreman'],
        ['name' => 'Кладовщик',                  'hourly_rate' => 400, 'default_role' => 'warehouseman'],
        ['name' => 'Мастер по ходовой',          'hourly_rate' => 500, 'default_role' => 'mechanic'],
        ['name' => 'Мастер по двигателю и КПП',  'hourly_rate' => 550, 'default_role' => 'mechanic'],
    ];

    public function run(): void
    {
        $keep = array_column(self::POSITIONS, 'name');

        // Сотрудников с удаляемых должностей открепляем (position_id = null),
        // чтобы не нарушить внешний ключ, затем удаляем сами должности.
        $obsolete = Position::whereNotIn('name', $keep)->pluck('id');
        if ($obsolete->isNotEmpty()) {
            User::whereIn('position_id', $obsolete)->update(['position_id' => null]);
            Position::whereIn('id', $obsolete)->delete();
        }

        foreach (self::POSITIONS as $data) {
            Position::updateOrCreate(
                ['name' => $data['name']],
                $data,
            );
        }

        $this->command->info('Должностей в справочнике: '.Position::count());
    }
}
