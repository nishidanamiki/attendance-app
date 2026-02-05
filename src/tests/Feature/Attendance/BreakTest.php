<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class BreakTest extends TestCase
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

    public function test_break_in_button_is_visible_when_user_is_working()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $this->createWorkingAttendance($user, $fixedNow->toDateString());

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('>休憩入<', false);

        Carbon::setTestNow();
    }

    public function test_break_in_creates_break_time_and_status_becomes_break()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $attendance = $this->createWorkingAttendance($user, $fixedNow->toDateString());

        $response = $this->actingAs($user)->post('/attendance/break-in');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_in_at' => $fixedNow->format('H:i:s'),
        ]);

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_break_out_button_works_and_status_becomes_working()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 12, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $attendance = $this->createWorkingAttendance($user, $fixedNow->toDateString());

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in_at' => '11:50',
        ]);

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('>休憩戻<', false);

        $response = $this->actingAs($user)->post('/attendance/break-out');
        $response->assertRedirect('/attendance');

        $break = BreakTime::where('attendance_id', $attendance->id)->latest('id')->first();
        $this->assertNotNull($break);
        $this->assertNotNull($break->break_out_at);

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_user_can_take_breaks_multiple_times_in_a_day()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 13, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $attendance = $this->createWorkingAttendance($user, $fixedNow->toDateString());

        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');
        $this->actingAs($user)->post('/attendance/break-out')->assertRedirect('/attendance');

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('>休憩入<', false);

        $this->actingAs($user)->post('/attendance/break-in')->assertRedirect('/attendance');

        $this->assertSame(
            2,
            BreakTime::where('attendance_id', $attendance->id)->count()
        );

        Carbon::setTestNow();
    }
}
