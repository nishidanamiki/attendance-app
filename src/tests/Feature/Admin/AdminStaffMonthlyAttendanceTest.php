<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class AdminStaffMonthlyAttendanceTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_see_selected_users_monthly_attendance()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staffA = $this->loginVerifiedUser(['name' => 'スタッフA', 'is_admin' => false]);
        $staffB = $this->loginVerifiedUser(['name' => 'スタッフB', 'is_admin' => false]);

        $attendanceA1 = $this->createBaseAttendance($staffA, '2026-02-01');
        $attendanceA1->update(['clock_in_at' => '09:10:00', 'clock_out_at' => '18:10:00']);

        $attendanceA2 = $this->createBaseAttendance($staffA, '2026-02-02');
        $attendanceA2->update(['clock_in_at' => '09:20:00', 'clock_out_at' => '18:20:00']);

        $attendanceB = $this->createBaseAttendance($staffB, '2026-02-01');
        $attendanceB->update(['clock_in_at' => '08:00:00', 'clock_out_at' => '17:00:00']);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staffA->id);

        $response->assertOk();

        $response->assertSee('スタッフAさんの勤怠');
        $response->assertSee('2026/02');

        $response->assertSee('02/01 (日)');
        $response->assertSee('09:10');
        $response->assertSee('18:10');

        $response->assertSee('02/02 (月)');
        $response->assertSee('09:20');
        $response->assertSee('18:20');

        $response->assertDontSee('スタッフB');
        $response->assertDontSee('08:00');
        $response->assertDontSee('17:00');
    }

    public Function test_clicking_previous_month_shows_previous_month_attendance()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $admin =$this->loginVerifiedUser(['name' =>'管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $januaryAttendance = $this->createBaseAttendance($staff, '2026-01-15');
        $januaryAttendance->update(['clock_in_at' => '09:30:00', 'clock_out_at' => '18:30:00']);

        $february = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id);
        $february->assertOk();
        $february->assertSee('2026/02');
        $february->assertSee('前月');
        $february->assertSee('/admin/attendance/staff/' . $staff->id . '?month=2026-01');

        $january = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-01');
        $january->assertOk();
        $january->assertSee('2026-01');
        $january->assertSee('01/15');
        $january->assertSee('09:30');
        $january->assertSee('18:30');
    }

    public function test_clicking_next_month_shows_next_month_attendance()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $marchAttendance = $this->createBaseAttendance($staff, '2026-03-15');
        $marchAttendance->update(['clock_in_at' => '09:10:00', 'clock_out_at' => '18:10:00']);

        $february = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id);
        $february->assertOk();
        $february->assertSee('2026/02');
        $february->assertSee('翌月');
        $february->assertSee('/admin/attendance/staff/' . $staff->id . '?month=2026-03');

        $march = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-03');
        $march->assertOk();
        $march->assertSee('2026/03');
        $march->assertSee('03/15');
        $march->assertSee('09:10');
        $march->assertSee('18:10');
    }

    public function test_clicking_detail_shows_the_day_attendance_detail()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $attendance = $this->createBaseAttendance($staff, '2026-02-04');
        $attendance->update(['clock_in_at' => '09:00:00', 'clock_out_at' => '18:00:00']);

        $monthly = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id);
        $monthly->assertOk();
        $monthly->assertSee('2026/02');
        $monthly->assertSee('/admin/attendance/' . $attendance->id);

        $detail = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);
        $detail->assertOk();
        $detail->assertSee('一般ユーザー');
        $detail->assertSee('09:00');
        $detail->assertSee('18:00');
    }
}
