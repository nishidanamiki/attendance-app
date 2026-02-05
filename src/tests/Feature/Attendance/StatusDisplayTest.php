<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_is_off_when_user_has_no_attendance_today()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    public function test_status_is_working_when_user_clocked_in_and_not_on_break()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $fixedNow->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_status_is_break_when_user_is_on_break()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $fixedNow->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in_at' => '12:00',
            'break_out_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_status_is_done_when_user_clocked_out()
    {
        $fixedNow =Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $fixedNow->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
