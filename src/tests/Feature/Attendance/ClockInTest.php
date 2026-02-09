<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_clock_in_button_is_visible_when_status_is_off()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee('出勤');
    }

    public function test_clock_in_creates_attendance_and_status_becomes_working()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $fixedNow->toDateString(),
        ]);

        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('出勤中');
    }

    public function test_user_cannot_clock_in_twice_in_a_day()
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
            'clock_out_at' => '18:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertDontSee('/attendance/clock-in', false);

        $this->actingAs($user)->post('/attendance/clock-in')->assertStatus(403);
    }

    public function test_clock_in_time_is_visible_on_attendance_list()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance/clock-in')->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertOk();
        $response->assertSee($fixedNow->format('H:i'));

    }
}
