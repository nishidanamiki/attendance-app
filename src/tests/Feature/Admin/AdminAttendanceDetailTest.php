<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    public function test_admin_can_see_selected_attendance_detail()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);

        $attendance = $this->createBaseAttendance($staff, '2026-02-02');
        $attendance->update([
            'clock_in_at' => '09:11:00',
            'clock_out_at' => '18:22:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertOk();
        $response->assertSee('一般ユーザー');
        $response->assertSee('2026年');
        $response->assertSee('2月2日');
        $response->assertSee('09:11');
        $response->assertSee('18:22');
    }

    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);
        $attendance = $this->createBaseAttendance($staff, '2026-02-02');

        $response = $this->actingAs($admin) ->from('/admin/attendance/' . $attendance->id)->patch('/admin/attendance/' .$attendance->id,[
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/attendance/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_validation_error_when_break_in_is_after_clock_out()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);
        $attendance = $this->createBaseAttendance($staff, '2026-02-02');

        $response = $this->actingAs($admin)->from('/admin/attendance/' .$attendance->id)->patch('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['id' => '', 'start' => '18:30', 'end' => '18:40'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/attendance/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('休憩時間が不適切な値です');
    }

    public function test_validation_error_when_break_out_is_after_clock_out()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);
        $attendance = $this->createBaseAttendance($staff, '2026-02-02');

        $response = $this->actingAs($admin)->from('/admin/attendance/' . $attendance->id)->patch('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['id' => '', 'start' => '17:30', 'end' => '18:30'],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/attendance/' . $attendance->id);
        $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_validation_error_when_remarks_is_missing()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
        $staff = $this->loginVerifiedUser(['name' => '一般ユーザー', 'is_admin' => false]);
        $attendance = $this->createBaseAttendance($staff, '2026-02-02');

        $response = $this->actingAs($admin)->from('/admin/attendance/' . $attendance->id)->patch('/admin/attendance/' . $attendance->id, [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [['id' => '', 'start' => '', 'end' => '']],
            'remarks' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/attendance/' . $attendance->id);
        $response->assertSessionHasErrors('remarks');
        $this->followRedirects($response)->assertSee('備考を記入してください');
    }
}
