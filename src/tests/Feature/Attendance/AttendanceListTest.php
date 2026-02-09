<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function loginVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' =>now(),
        ]);
    }

    public function test_attendance_list_shows_all_of_my_attendances_in_month()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();
        $other =User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '08:02:00',
            'clock_out_at' => '17:02:00',
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-03',
            'clock_in_at' => '08:03:00',
            'clock_out_at' => '17:03:00',
        ]);

        Attendance::create([
            'user_id' => $other->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '06:59:00',
            'clock_out_at' => '19:59:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertOk();

        $response->assertSee('02/02');
        $response->assertSee('08:02');
        $response->assertSee('17:02');

        $response->assertSee('02/03');
        $response->assertSee('08:03');
        $response->assertSee('17:03');

        $response->assertDontSee('06:59');
        $response->assertDontSee('19:59');
    }

    public function test_attendance_list_shows_current_month_on_first_visit()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertOk();
        $response->assertSee('2026/02');
    }

    public function test_prev_month_is_shows_previous_month_attendance()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-01-20',
            'clock_in_at' => '07:01:00',
            'clock_out_at' => '16:01:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '08:02:00',
            'clock_out_at' => '17:02:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-01');
        $response->assertOk();

        $response->assertSee('01/20');
        $response->assertSee('07:01');
        $response->assertSee('16:01');

        $response->assertDontSee('02/02');
        $response->assertDontSee('08:02');
        $response->assertDontSee('17:02');
    }

    public function test_next_month_shows_next_month_attendances()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user =$this->loginVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '08:02:00',
            'clock_out_at' => '17:02:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-05',
            'clock_in_at' => '09:03:00',
            'clock_out_at' => '18:03:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');
        $response->assertOk();

        $response->assertSee('03/05');
        $response->assertSee('09:03');
        $response->assertSee('18:03');

        $response->assertDontSee('02/02');
        $response->assertDontSee('08:02');
        $response->assertDontSee('17:02');
    }

    public function test_click_detail_navigates_to_attendance_detail_page()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '08:02:00',
            'clock_out_at' => '17:02:00',
        ]);

        $list = $this->actingAs($user)->get('/attendance/list');
        $list->assertOk();

        $list->assertSee(route('attendance.detail', ['id' => $attendance->id]), false);

        $detail = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $detail->assertOk();

        $detail->assertSee('08:02');
        $detail->assertSee('17:02');
    }
}
