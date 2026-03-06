<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\App;

class StaffInformationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_view_all_user_names_and_emails()
    {
        /** @var \App\Models\User $user */
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->get("/admin/staff/list");

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);
    }

    public function test_admin_can_see_user_attendance_information()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
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

        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$user->id}");

        foreach ([$attendance1, $attendance2] as $attendance) {
            $response->assertSee(
                Carbon::parse($attendance->start_time)->format('H:i')
            );
            $response->assertSee(
                Carbon::parse($attendance->end_time)->format('H:i')
            );

            $breakMinutes = $attendance->calculateTotalBreakTime();
            $breakHours = floor($breakMinutes / 60);
            $breakMins = $breakMinutes % 60;

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

        $response->assertDontSee($otherUser->name);
    }

    public function test_previous_month_information_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
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

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertSee("/admin/attendance/staff/{$user->id}?month=2026-02");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-02");

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

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
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

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertSee("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=2026-03");

        $response->assertStatus(200);
        $response->assertSee('2026/03');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
    }

    public function test_admin_can_view_attendance_detail_from_staff_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15));
        App::setLocale('ja');

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $listResponse = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}");

        $listResponse->assertSee("/admin/attendance/{$user->id}/2026-03-01");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$user->id}/2026-03-01");
        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('3月1日');
    }
}
