<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class StampPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_page_displays_current_datetime()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 9, 30, 0));

        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        App::setLocale('ja');

        $expectedDate = now()->translatedFormat('Y年n月j日(D)');
        $expectedTime = now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
