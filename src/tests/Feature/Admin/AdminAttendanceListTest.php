<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    private function admin()
    {
        return $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true]);
    }

    private function listUrl(?string $date = null): string
    {
        $base = '/admin/attendance/list';
        return $date ? "{$base}?date={$date}" : $base;
    }

    public function test_admin_can_see_all_users_attendance_of_the_day()
    {
        $admin = $this->admin();

        $userA = $this->loginVerifiedUser(['name' => 'ユーザーA']);
        $userB = $this->loginVerifiedUser(['name' => 'ユーザーB']);

        $this->createBaseAttendance($userA, '2026-02-02')->update(['clock_in_at' => '09:01:00', 'clock_out_at' => '18:01:00']);
        $this->createBaseAttendance($userB, '2026-02-02')->update(['clock_in_at' => '09:02:00', 'clock_out_at' => '18:02:00']);

        $response = $this->actingAs($admin)->get($this->listUrl('2026-02-02'));
        $response->assertOk();
        $response->assertSee('09:01')->assertSee('18:01');
        $response->assertSee('09:02')->assertSee('18:02');
    }

    public function test_list_page_shows_current_date_when_opened()
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get($this->listUrl('2026-02-02'));
        $response->assertOk();
        $response->assertSee('2026/02/02');
    }

    public function test_clicking_previous_day_shows_previous_date_attendance()
    {
        $admin = $this->admin();

        $user = $this->loginVerifiedUser(['name'=> 'ユーザー']);
        $this->createBaseAttendance($user, '2026-02-02');
        $this->createBaseAttendance($user, '2026-02-01');

        $response = $this->actingAs($admin)->get($this->listUrl('2026-02-02'));
        $response->assertOk()->assertSee('2026/02/02');

        $response->assertSee('前日');
        $response->assertSee($this->listUrl('2026-02-01'));

        $prev = $this->actingAs($admin)->get($this->listUrl('2026-02-01'));
        $prev->assertOk()->assertSee('2026/02/01');
    }

    public function test_clicking_next_day_shows_next_date_attendance()
    {
        $admin = $this->admin();

        $user = $this->loginVerifiedUser(['name' => 'ユーザー']);
        $this->createBaseAttendance($user, '2026-02-02');
        $this->createBaseAttendance($user, '2026-02-03');

        $response = $this->actingAs($admin)->get($this->listUrl('2026-02-02'));
        $response->assertOk()->assertSee('2026/02/02');

        $response->assertSee('翌日');
        $response->assertSee($this->listUrl('2026-02-03'));

        $next = $this->actingAs($admin)->get($this->listUrl('2026-02-03'));
        $next->assertOk()->assertSee('2026/02/03');
    }
}
