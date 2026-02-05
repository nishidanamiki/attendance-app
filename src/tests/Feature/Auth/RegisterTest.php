<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_shows_error_when_name_is_missing()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('name');
        $this->followRedirects($response)->assertSee('お名前を入力してください');
    }

    public function test_register_shows_error_when_email_is_missing()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->followRedirects($response)->assertSee('メールアドレスを入力してください');
    }

    public function test_register_shows_error_when_password_is_less_than_8_chars()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->followRedirects($response)->assertSee('パスワードは8文字以上で入力してください');
    }

    public function test_register_shows_error_when_password_confirmation_does_not_match()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password987',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->followRedirects($response)->assertSee('パスワードと一致しません');
    }

    public function test_register_shows_error_when_password_is_missing()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->followRedirects($response)->assertSee('パスワードを入力してください');
    }

    public function test_register_succeeds_and_user_is_saved()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/attendance');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $this->get('/attendance')->assertRedirect('/email/verify');
    }
}
