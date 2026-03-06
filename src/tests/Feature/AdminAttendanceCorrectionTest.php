<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\App;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_view_pending_correction_requests()
    {
        /** @var \App\Models\User $user */
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user3 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-02',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2026-03-03',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:00:00',
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'new_start_time' => '10:15:00',
            'new_end_time' => '19:00:00',
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance3->id,
            'user_id' => $user3->id,
            'new_start_time' => '08:15:00',
            'new_end_time' => '17:00:00',
            'approval_status' => Application::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/list?tab=pending");

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertDontSee($user3->name);
    }

    public function test_admin_can_view_approved_correction_requests()
    {
        /** @var \App\Models\User $user */
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user3 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-02',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2026-03-03',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:00:00',
            'approval_status' => Application::STATUS_APPROVED,
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'new_start_time' => '10:15:00',
            'new_end_time' => '19:00:00',
            'approval_status' => Application::STATUS_APPROVED,
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance3->id,
            'user_id' => $user3->id,
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/list?tab=approved");

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertDontSee($user3->name);
    }

    public function test_admin_can_view_correction_request_detail()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $application = Application::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/list?tab=pending");

        $response->assertSee("/stamp_correction_request/approve/{$application->id}");

        $response = $this->actingAs($admin, 'admin')
            ->get("/stamp_correction_request/approve/{$application->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:15');
        $response->assertSee('18:00');
    }

    public function test_admin_can_approve_correction_request_and_update_attendance()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        $application = Application::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'new_start_time' => '09:15:00',
            'new_end_time' => '18:00:00',
        ]);

        ApplicationBreak::factory()->create([
            'application_id' => $application->id,
            'new_break_start_time' => '12:30:00',
            'new_break_end_time' => '13:30:00',
        ]);

        $this->actingAs($admin, 'admin')
            ->post("/stamp_correction_request/approve/{$application->id}");

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'approval_status' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:15:00',
            'end_time' => '18:00:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:30:00',
            'break_end_time' => '13:30:00',
        ]);

        $this->assertDatabaseMissing('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);
    }
}
