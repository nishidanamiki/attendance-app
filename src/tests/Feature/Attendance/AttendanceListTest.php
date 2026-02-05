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

    private function loginVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' =>now(),
        ]);
    }

    // 自分が行った勤怠情報が全て表示されている
    public function test_user_sees_only_own_attendances_on_list()
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

        $response->assertSee('08:02');
        $response->assertSee('08:03');

        $response->assertDontSee('06:59');

        Carbon::setTestNow();
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_attendance_list_shows_current_month_by_default()
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

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertOk();

        $response->assertSee('08:02');
        $response->assertDontSee('07:01');

        Carbon::setTestNow();
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_is_displayed_when_month_query_is_previous()
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

        $response->assertSee('07:01');
        $response->assertDontSee('08:02');

        Carbon::setTestNow();
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_next_month_is_displayed_when_month_query_is_next()
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

        $response->assertSee('09:03');
        $response->assertDontSee('08:02');

        Carbon::setTestNow();
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_detail_link_navigates_to_attendance_detail()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = $this->loginVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-02',
            'clock_in_at' => '08:02:00',
            'clock_out_At' => '17:02:00',
        ]);

        $list = $this->actingAs($user)->get('/attendance/list');
        $list->assertOk();

        $list->assertSee('詳細');

        $detail = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $detail->assertOk();

        Carbon::setTestNow();
    }
}
