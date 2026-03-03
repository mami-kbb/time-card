<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class StampStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_attendance_page_displays_current_datetime()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        App::setLocale('ja');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $expectedDate = now()->translatedFormat('Y年n月j日(D)');
        $expectedTime = now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    public function test_user_sees_off_duty_status_when_no_attendance_today()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_user_sees_working_status_when_started_but_not_finished()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_user_sees_breaking_status_when_on_active_break()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => now(),
            'break_end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_user_sees_finished_status_when_work_has_ended()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
            'end_time' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
