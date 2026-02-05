<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Psy\Util\Str;
use Tests\TestCase;

class StampCorrectionApproveTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_admin_can_open_approve_page()
    {
        $admin = $this->loginVerifiedUser(['is_admin' => true, 'name' => '管理者']);
        $user = $this->loginVerifiedUser(['is_admin' => false, 'name' => '一般ユーザー']);

        $attendance = $this->createAttendance($user);
        $request = $this->createPendingRequest($user, $attendance);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/approve/' . $request->id);
        $response->assertOk();
        $response->assertSee('管理者承認テスト');
    }

    public function test_admin_can_approve_request_and_it_appears_in_approved_list()
    {
        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $admin = $this->loginVerifiedUser(['is_admin' => true, 'name' => '管理者']);
        $user = $this->loginVerifiedUser(['is_admin' => false, 'name' => '一般ユーザー']);

        $attendance = $this->createAttendance($user);
        $request = $this->createPendingRequest($user, $attendance);

        $approve = $this->actingAs($admin)->patch('/stamp_correction_request/approve/' . $request->id);
        $approve->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $list = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');
        $list->assertOk();
        $list->assertSee('管理者承認テスト');

        Carbon::setTestNow();
    }
}
