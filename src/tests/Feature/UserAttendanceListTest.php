<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_user_can_see_all_of_their_attendances()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $otherUser */
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-02',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-03-03',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance2->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);

        foreach ([$attendance1, $attendance2] as $attendance) {
            $response->assertSee(
                Carbon::parse($attendance->start_time)->format('H:i')
            );
            $response->assertSee(
                Carbon::parse($attendance->end_time)->format('H:i')
            );

            $breakMinutes = $attendance->calculateTotalBreakTime();
            $breakHours = floor($breakMinutes / 60);
            $breakMins =$breakMinutes % 60;

            if ($breakMinutes > 0) {
                $response->assertSee(
                    $breakHours . ':' . str_pad($breakMins, 2, '0', STR_PAD_LEFT)
                );
            }

            $workMinutes = $attendance->calculateTotalWorkTime();
            $workHours   = floor($workMinutes / 60);
            $workMins    = $workMinutes % 60;

            $response->assertSee(
                $workHours . ':' . str_pad($workMins, 2, '0', STR_PAD_LEFT)
            );
        }

        $response->assertDontSee('08:00');
    }

    public function test_current_month_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2026/03');
    }

    public function test_previous_month_information_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $response = $this->actingAs($user)
        ->get('/attendance/list?month=2026-02');

        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
    }

    public function test_next_month_information_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 15));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-10',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
    }

    public function test_user_can_see_the_day_detail_from_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));
        App::setLocale('ja');

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

        $listResponse = $this->actingAs($user)
            ->get('/attendance/list');

        $listResponse->assertSee('/attendance/detail/2026-03-01');

        $response = $this->actingAs($user)
        ->get('/attendance/detail/2026-03-01');
        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('3月1日');
    }
}
