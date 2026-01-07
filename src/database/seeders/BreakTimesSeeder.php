<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::updateOrCreate(
                [
                    'attendance_id' => $attendance->id,
                    'break_in_at' => '12:00:00',
                ],
                [
                    'break_out_at' => '13:00:00',
                ]
            );
        }
    }
}
