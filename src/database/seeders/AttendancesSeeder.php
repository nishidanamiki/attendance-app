<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffs = User::where('is_admin', false)->get();

        foreach ($staffs as $staff) {
            for ($day = 1; $day <= 5; $day++) {
                $date = Carbon::now()->startOfMonth()->addDays($day - 1);

                Attendance::updateOrCreate(
                    [
                        'user_id' => $staff->id,
                        'work_date' => $date->toDateString()
                    ],
                    [
                        'clock_in_at' => '09:00:00',
                        'clock_out_at' => '18:00:00',
                    ]
                );
            }
        }
    }
}
