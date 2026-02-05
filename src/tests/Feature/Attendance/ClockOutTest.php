<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    private function loginVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function createWorkingAttendance(User $user, string $workDate): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);
    }

    public function test_clock_out_button_works_and_status_becomes_done()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 18, 5, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $attendance = $this->createWorkingAttendance($user, $fixedNow->toDateString());

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('>退勤<', false);

        $response = $this->actingAs($user)->post('/attendance/clock-out');
        $response->assertRedirect('/attendance');

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out_at);
        $this->assertSame($fixedNow->format('H:i:s'), $attendance->clock_out_at);

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('退勤済')->assertSee('お疲れ様でした。');

        Carbon::setTestNow();
    }

    public function test_clock_out_time_is_visible_on_attendance_list()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 18, 5, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $fixedNow->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => $fixedNow->format('H:i:s'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertOk();
        $response->assertSee('18:05');

        Carbon::setTestNow();
    }
}
