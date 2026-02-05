<?php

namespace Tests\Feature\StampCorrectionRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\StampCorrectionRequest;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class StampCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    private string $pending = 'pending';
    private string $approved = 'approved';

    public function test_store_creates_stamp_correction_request()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->actingAs($user)->post(route('stamp_correction_request.store'), [
            'work_date' => $attendance->work_date,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:10',
            'clock_out_at' => '18:10',
            'breaks' => [
                ['id' => '', 'start' => '', 'end' => ''],
            ],
            'remarks' => '電車遅延のため',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'remarks' => '電車遅延のため',
        ]);
    }

    public function test_error_message_when_clock_in_is_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance,[
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
        ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_message_when_break_in_is_before_clock_in_or_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, [
            'breaks' => [
                ['id' => '', 'start' => '08:00', 'end' => '08:30'],
            ],
        ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('休憩時間が不適切な値です');
    }

    public function test_error_message_when_break_out_is_after_clock_out()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, [
            'breaks' => [
                ['id' => '', 'start' => '17:30', 'end' => '18:30'],
            ],
        ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_message_when_remarks_is_missing()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user);

        $response = $this->postCorrection($user, $attendance, [
            'remarks' => '',
        ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $response->assertSessionHasErrors('remarks');
        $this->followRedirects($response)->assertSee('備考を記入してください');
    }

    public function test_pending_list_shows_all_own_requests()
    {
        $user = $this->loginVerifiedUser();
        $attendance1 = $this->createBaseAttendance($user, '2026-02-01');
        $attendance2 = $this->createBaseAttendance($user, '2026-02-02');

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'work_date' => $attendance1->work_date,
            'status' => $this->pending,
            'remarks' => '申請A',
        ]);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'work_date' => $attendance2->work_date,
            'status' => $this->pending,
            'remarks' => '申請B',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');
        $response->assertOk();
        $response->assertSee('申請A');
        $response->assertSee('申請B');
    }

    public function test_approved_list_shows_approved_requests()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user, '2026-02-02');

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => $this->approved,
            'remarks' => '承認済み申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertOk();
        $response->assertSee('承認済み申請');
    }

    public function test_request_detail_link_navigates_to_attendance_detail()
    {
        $user = $this->loginVerifiedUser();
        $attendance = $this->createBaseAttendance($user, '2026-02-02');

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => $this->pending,
            'remarks' => '詳細遷移テスト',
        ]);

        $list = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');
        $list->assertOk();
        $list->assertSee('詳細');

        $detail = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $detail->assertOk();
    }

    public function test_admin_can_see_pending_request_on_admin_list()
    {
        $user = $this->loginVerifiedUser(['name' => '一般ユーザー']);
        $attendance = $this->createBaseAttendance($user);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => $this->pending,
            'remarks' => '管理者確認用',
        ]);

        $admin = $this->loginVerifiedUser([
            'name' => '管理者',
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertOk();
        $response->assertSee('管理者確認用');
        $response->assertSee('一般ユーザー');
    }
}
