<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function loginVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'name' => 'テスト太郎',
        ], $attrs));
    }

    public function test_detail_shows_logged_in_user_name()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertOk();

        $response->assertSee('山田太郎');
    }

    public function test_detail_shows_selected_date()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertOk();

        $response->assertSee('2026年');
        $response->assertSee('2月2日');
    }

    public function test_detail_shows_clock_in_and_out_times_match_records()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '09:01:00',
            'clock_out_at' => '18:02:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertOk();

        $response->assertSee('09:01');
        $response->assertSee('18:02');
    }

    public function test_detail_shows_break_times_match_records()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in_at' => '12:10:00',
            'break_out_at' => '12:40:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertOk();

        $response->assertSee('12:10');
        $response->assertSee('12:40');
    }
}
