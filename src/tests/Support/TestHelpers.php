<?php

namespace Tests\Support;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Testing\TestResponse;

trait TestHelpers
{
    protected function loginVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'name' => 'テスト太郎',
        ], $attrs));
    }

    protected function createBaseAttendance(User $user, string $workDate = '2026-02-02'): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);
    }

    protected function postCorrection(User $user, Attendance $attendance, array $override = []): TestResponse
    {
        $base = [
            'work_date' => $attendance->work_date,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                ['id' => '', 'start' => '', 'end' => ''],
            ],
            'remarks' => 'テスト',
        ];

        $payload = array_replace_recursive($base, $override);

        return $this->from('/attendance/detail/' . $attendance->id)
            ->actingAs($user)
            ->post(route('stamp_correction_request.store'), $payload);
    }

    protected function assertCorrectionValidationError(TestResponse $response, Attendance $attendance, string $message, ?string $field = null): void
    {
        $response->assertRedirect('/attendance/detail/' . $attendance->id);
        $field ? $response->assertSessionHasErrors($field) : $response->assertSessionHasErrors();
        $this->followRedirects($response)->assertSee($message);
    }
}
