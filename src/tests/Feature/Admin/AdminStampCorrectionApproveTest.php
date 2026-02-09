<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Tests\Support\TestHelpers;
use Carbon\Carbon;
use Tests\TestCase;

class AdminStampCorrectionApproveTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    private function loginVerifiedUser(array $attrs = []): User{
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attrs));
    }

    private function createAttendance(User $user, String $workDate = '2026-02-02'): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);
    }

    private function createPendingRequest(User $user, Attendance $attendance): StampCorrectionRequest
    {
        return StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => 'pending',
            'remarks' => '管理者承認テスト',
            'clock_in_at' => '09:10',
            'clock_out_at' => '18:10',
        ]);
    }

    public function test_pending_tab_shows_all_users_pending_requests()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $userA = $this->loginVerifiedUser(['name' => 'スタッフA', 'is_admin' => false]);
        $userB = $this->loginVerifiedUser(['name' => 'スタッフB', 'is_admin' => false]);

        $attendanceA = $this->createBaseAttendance($userA, '2026-02-02');
        $attendanceB = $this->createBaseAttendance($userB, '2026-02-03');

        StampCorrectionRequest::create([
            'user_id' => $userA->id,
            'attendance_id' => $attendanceA->id,
            'work_date' => $attendanceA->work_date,
            'status' => 'pending',
            'remarks' => '未認証A',
            'clock_in_at' => '09:10:10',
            'clock_out_at' => '18:10:00',
        ]);

        StampCorrectionRequest::create([
            'user_id' => $userB->id,
            'attendance_id' => $attendanceB->id,
            'work_date' => $attendanceB->work_date,
            'status' => 'pending',
            'remarks' => '未認証B',
            'clock_in_at' => '09:20:00',
            'clock_out_at' => '18:20:00',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');

        $response->assertOk();
        $response->assertSee('未認証A');
        $response->assertSee('未認証B');
        $response->assertSee('スタッフA');
        $response->assertSee('スタッフB');
    }

    public function test_approved_tab_shows_all_users_approved_requests()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $userA = $this->loginVerifiedUser(['name' => 'スタッフA', 'is_admin' => false]);
        $userB = $this->loginVerifiedUser(['name' => 'スタッフB', 'is_admin' => false]);

        $attendanceA = $this->createBaseAttendance($userA, '2026-02-02');
        $attendanceB = $this->createBaseAttendance($userB, '2026-02-03');

        StampCorrectionRequest::create([
            'user_id' => $userA->id,
            'attendance_id' => $attendanceA->id,
            'work_date' => $attendanceA->work_date,
            'status'=> 'approved',
            'remarks' => '承認済A',
            'clock_in_at' => '09:10:00',
            'clock_out_at' => '18:10:00',
        ]);

        StampCorrectionRequest::create([
            'user_id' => $userB->id,
            'attendance_id' => $attendanceB->id,
            'work_date' => $attendanceB->work_date,
            'status' => 'approved',
            'remarks' => '承認済B',
            'clock_in_at' => '09:20:00',
            'clock_out_at' => '18:20:00',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');
        $response->assertOk();
        $response->assertSee('承認済A');
        $response->assertSee('承認済B');
        $response->assertSee('スタッフA');
        $response->assertSee('スタッフB');
    }

    public function test_admin_can_see_request_detail_content_correctly()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $user = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $attendance = $this->createBaseAttendance($user, '2026-02-02');
        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => 'pending',
            'remarks' => '詳細表示テスト',
            'clock_in_at' => '09:30:00',
            'clock_out_at' => '18:40:00',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/approve/' . $request->id);
        $response->assertOk();
        $response->assertSee('一般ユーザー');
        $response->assertSee('詳細表示テスト');
        $response->assertSee('09:30');
        $response->assertSee('18:40');
    }

    public function test_admin_can_approve_request_and_attendance_is_updated()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $user = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $attendance = $this->createBaseAttendance($user, '2026-02-02');
        $attendance->update([
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => 'pending',
            'remarks' => '承認更新テスト',
            'clock_in_at' => '09:10:00',
            'clock_out_at' => '18:10:00',
        ]);

        $response = $this->actingAs($admin)->patch('/stamp_correction_request/approve/' . $request->id);
        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_at' => '09:10:00',
            'clock_out_at' => '18:10:00',
        ]);
    }
}
