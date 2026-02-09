<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\User;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $user = User::where('email', 'test@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_clicking_verify_link_on_notice_page_shows_mailhog_link()
    {
        $user = User::factory()->unverified()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));
        $response->assertOk();
        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025', false);
        $response->assertSee('target="_blank"', false);
    }

    public function test_email_verification_completes_and_redirects_to_attendance_page()
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
