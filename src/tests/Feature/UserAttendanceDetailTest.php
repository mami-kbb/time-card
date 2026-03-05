<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\App;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_detail_page_displays_logged_in_user_name()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-03-01');
        $response->assertStatus(200);
        $response->assertSeeText($user->name);
    }

    public function test_detail_page_displays_selected_date()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        App::setLocale('ja');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-03-01');
        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('3月1日');
    }

    public function test_detail_page_displays_correct_start_and_end_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:15:00',
            'end_time' => '18:15:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-03-01');
        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:15');
    }

    public function test_detail_page_displays_correct_break_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:15:00',
            'end_time' => '18:15:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-03-01');
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}
