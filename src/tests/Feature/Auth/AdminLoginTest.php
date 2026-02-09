<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_shows_error_when_email_is_missing()
    {
        $response = $this->from('/admin/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->followRedirects($response)->assertSee('メールアドレスを入力してください');
    }

    public function test_admin_login_shows_error_when_password_is_missing()
    {
        $response = $this->from('/admin/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('password');
        $this->followRedirects($response)->assertSee('パスワードを入力してください');
    }

    public function test_admin_login_shows_error_when_credentials_are_invalid()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $response = $this->from('/admin/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->followRedirects($response)->assertSee('ログイン情報が登録されていません');
    }

    public function test_admin_can_access_admin_pages()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);

        $response = $this->get('/admin/staff/list');
        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_pages()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->get('/admin/attendance/list');
        $response->assertRedirect('/attendance');
    }
}
