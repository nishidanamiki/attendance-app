<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    public function test_admin_can_see_all_users_name_and_email_on_staff_list()
    {
        $admin = $this->loginVerifiedUser(['name' => '管理者', 'is_admin' => true, 'email' => 'admin@example.com']);
        $staffA = $this->loginVerifiedUser(['name' => 'スタッフA', 'is_admin' => false, 'email' => 'staffa@example.com']);
        $staffB = $this->loginVerifiedUser(['name' => 'スタッフB', 'is_admin' => false, 'email' => 'staffb@example.com']);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertOk();
        $response->assertSee('スタッフA')->assertSee('staffa@example.com');
        $response->assertSee('スタッフB')->assertSee('staffb@example.com');
        $response->assertDontSeeText('admin@example.com');
    }
}
