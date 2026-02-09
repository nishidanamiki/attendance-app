<?php

namespace Tests\Feature\StampCorrectionRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\StampCorrectionRequest;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    private string $pending = 'pending';
    private string $approved = 'approved';

    private function makeRequest($user, $attendance, array $attrs = []): StampCorrectionRequest
    {
        return StampCorrectionRequest::create(array_merge([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => $this->pending,
            'remarks' => '申請',
        ], $attrs));
    }

    private function list($user, string $tab)
    {
        return $this->actingAs($user)->get(route('stamp_correction_request.list', ['tab' => $tab]));
    }

    public function test_store_creates_stamp_correction_request()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'work_date' => $attendance->work_date,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:10',
            'clock_out_at' => '18:10',
            'breaks' => [['id' => '', 'start' => '', 'end' => '']],
            'remarks' => '電車遅延のため',
        ])->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'clock_in_at' => '09:10:00',
            'clock_out_at' => '18:10:00',
            'remarks' => '電車遅延のため',
            'status' => $this->pending,
        ]);
    }

    public function test_clock_in_after_clock_out_is_invalid()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, ['clock_in_at' => '19:00', 'clock_out_at' => '18:00']);
        $this->assertCorrectionValidationError($response, $attendance, '出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_break_in_after_clock_out_is_invalid()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, [
            'breaks' => [['id' => '', 'start' => '18:30', 'end' => '18:40']]
        ]);

        $this->assertCorrectionValidationError($response, $attendance, '休憩時間が不適切な値です');
    }

    public function test_break_out_after_clock_out_is_invalid()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, [
            'breaks' => [['id' => '', 'start' => '17:30', 'end' => '18:30']]
        ]);

        $this->assertCorrectionValidationError($response, $attendance, '休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_remarks_is_required()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, ['remarks' => '']);
        $this->assertCorrectionValidationError($response, $attendance, '備考を記入してください', 'remarks');
    }

    public function test_pending_list_shows_all_own_requests()
    {
        $user = $this->loginVerifiedUser();
        $attendance1 = $this->createBaseAttendance($user, '2026-02-01');
        $attendance2 = $this->createBaseAttendance($user, '2026-02-02');

        $this->makeRequest($user, $attendance1, ['remarks' => '申請A']);
        $this->makeRequest($user, $attendance2, ['remarks' => '申請B']);

        $this->list($user, 'pending')->assertOk()->assertSee('申請A')->assertSee('申請B');
    }

    public function test_approved_tab_shows_request_after_admin_approves()
    {
        $user = $this->loginVerifiedUser(['name' => '一般ユーザー']);
        $attendance = $this->createBaseAttendance($user);
        $request = $this->makeRequest($user, $attendance, ['remarks' => '承認済みテスト']);

        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);

        $this->actingAs($admin)->patch('stamp_correction_request/approve/' . $request->id)->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', ['id' => $request->id, 'status' => $this->approved]);
        $this->list($user, 'approved')->assertOk()->assertSee('承認済みテスト');
    }

    public function test_request_detail_link_navigates_to_attendance_detail()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user, '2026-02-02');
        $request = $this->makeRequest($user, $attendance, ['remarks' => '詳細遷移テスト']);

        $list = $this->list($user, 'pending')->assertOk()->assertSee('詳細');
        $list->assertSee('/stamp_correction_request/' . $request->id);

        $this->actingAs($user)->get('/stamp_correction_request/' . $request->id)->assertOk();
    }

    public function test_admin_can_see_request_on_approve_screen()
    {
        $user = $this->loginVerifiedUser(['name' => '一般ユーザー']);
        $attendance = $this->createBaseAttendance($user);
        $request = $this->makeRequest($user, $attendance, ['remarks' => '管理者承認画面テスト']);

        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);

        $this->actingAs($admin)->get('stamp_correction_request/approve/' . $request->id)->assertOk()->assertSee('管理者承認画面テスト')->assertSee('一般ユーザー');
    }
}
