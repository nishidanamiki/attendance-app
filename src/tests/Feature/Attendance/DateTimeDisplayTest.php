<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_datetime_is_displayed_in_the_same_format_as_ui()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $fixedNow = Carbon::create(2026, 2, 4, 10, 15, 0, 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertOk();
        $response->assertSee($fixedNow->locale('ja')->isoFormat('YYYY年MM月DD日(ddd)'));
        $response->assertSee($fixedNow->format('H:i'));

        Carbon::setTestNow();
    }
}
