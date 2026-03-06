<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\App;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_user_can_see_the_day_detail_from_attendance_list()
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
            'work_date' => '2026-03-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $listResponse = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/list?date=2026-03-15");

        $listResponse->assertSee("/admin/attendance/{$user->id}/2026-03-15");

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$user->id}/2026-03-15");
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2026年');
        $response->assertSee('3月15日');
    }

    public function test_validation_fails_when_start_time_is_after_end_time()
    {
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

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$user->id}/2026-03-01")
            ->post("/admin/attendance/{$user->id}/2026-03-01", [
                'new_start_time' => '19:00',
                'new_end_time' => '18:00',
            ]);

        $response->assertRedirect("/admin/attendance/{$user->id}/2026-03-01");
        $response->assertSessionHasErrors([
            'new_start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_validation_fails_when_break_start_time_is_after_end_time()
    {
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

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$user->id}/2026-03-01")
            ->post("/admin/attendance/{$user->id}/2026-03-01", [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'breaks' => [
                    [
                        'new_break_start_time' => '19:00',
                        'new_break_end_time' => '19:30',
                    ]
                ]
            ]);

        $response->assertRedirect("/admin/attendance/{$user->id}/2026-03-01");
        $response->assertSessionHasErrors([
            'breaks.0.new_break_start_time' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_validation_fails_when_break_end_time_is_after_end_time()
    {
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

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$user->id}/2026-03-01")
            ->post("/admin/attendance/{$user->id}/2026-03-01", [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'breaks' => [
                    [
                        'new_break_start_time' => '17:30',
                        'new_break_end_time' => '18:30',
                    ]
                ]
            ]);

        $response->assertRedirect("/admin/attendance/{$user->id}/2026-03-01");
        $response->assertSessionHasErrors([
            'breaks.0.new_break_end_time' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_comment_is_required()
    {
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

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$user->id}/2026-03-01")
            ->post("/admin/attendance/{$user->id}/2026-03-01", [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'comment' => null,
            ]);

        $response->assertRedirect("/admin/attendance/{$user->id}/2026-03-01");
        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }
}