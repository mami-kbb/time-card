<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\App;

class UserAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_validation_fails_when_start_time_is_after_end_time()
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

        $response = $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
            'new_start_time' => '19:00',
            'new_end_time' => '18:00',
        ]);

        $response->assertRedirect('/attendance/detail/2026-03-01');
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

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'breaks' => [
                    [
                        'new_break_start_time' => '19:00',
                        'new_break_end_time' => '19:30',
                    ]
                ]
            ]);

        $response->assertRedirect('/attendance/detail/2026-03-01');
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

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'breaks' => [
                    [
                        'new_break_start_time' => '17:30',
                        'new_break_end_time' => '18:30',
                    ]
                ]
            ]);

        $response->assertRedirect('/attendance/detail/2026-03-01');
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

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:00',
                'new_end_time' => '18:00',
                'comment' => null,
            ]);

        $response->assertRedirect('/attendance/detail/2026-03-01');
        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }

    public function test_user_can_store_an_application()
    {
        App::setLocale('ja');

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

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance->id,
            'new_start_time' => '09:30:00',
            'new_end_time' => '18:00:00',
            'comment' => '電車遅延のため時間変更',
            'approval_status' => 0,
        ]);

        $application = Application::first();

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=pending');

        $response->assertSeeText($user->name);
        $response->assertSee('2026/03/01');

        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/approve/{$application->id}");

        $response->assertSeeText($user->name);
        $response->assertSee('09:30');
        $response->assertSee('18:00');
        $response->assertSeeText('電車遅延のため時間変更');
    }

    public function test_user_can_see_all_of_their_pending_applications()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
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

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance1->id,
            'new_start_time' => '09:30:00',
            'new_end_time' => '18:00:00',
            'comment' => '電車遅延のため時間変更',
            'approval_status' => 0,
        ]);

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-02')
            ->post('/attendance/detail/2026-03-02', [
                'new_start_time' => '10:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance2->id,
            'new_start_time' => '10:30:00',
            'new_end_time' => '18:00:00',
            'comment' => '電車遅延のため時間変更',
            'approval_status' => 0,
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-03-03',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        Application::factory()->create([
            'attendance_id' => $attendance3->id,
            'user_id' => $otherUser->id,
            'new_start_time' => '08:15:00',
            'new_end_time' => '17:00:00',
        ]);

        $response = $this->actingAs($user)
        ->get('/stamp_correction_request/list?tab=pending');

        $response->assertSee('2026/03/01');
        $response->assertSee('2026/03/02');
        $response->assertDontSee('2026/03/03');
    }

    public function test_user_can_see_all_of_their_approved_applications()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
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

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance1->id,
            'new_start_time' => '09:30:00',
            'new_end_time' => '18:00:00',
            'comment' => '電車遅延のため時間変更',
            'approval_status' => 0,
        ]);

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-02')
            ->post('/attendance/detail/2026-03-02', [
                'new_start_time' => '10:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $this->assertDatabaseHas('applications', [
            'attendance_id' => $attendance2->id,
            'new_start_time' => '10:30:00',
            'new_end_time' => '18:00:00',
            'comment' => '電車遅延のため時間変更',
            'approval_status' => 0,
        ]);

        $application1 = Application::where('attendance_id', $attendance1->id)->first();
        $application2 = Application::where('attendance_id', $attendance2->id)->first();

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin, 'admin')->from('/stamp_correction_request/approve/{$application1->id}')
        ->post("/stamp_correction_request/approve/{$application1->id}");

        $this->assertDatabaseHas('applications', [
            'id' => $application1->id,
            'approval_status' => Application::STATUS_APPROVED,
        ]);

        $this->actingAs($admin, 'admin')->from("/stamp_correction_request/approve/{$application2->id}")
            ->post("/stamp_correction_request/approve/{$application2->id}");

        $this->assertDatabaseHas('applications', [
            'id' => $application2->id,
            'approval_status' => Application::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance1->id,
            'start_time' => '09:30:00',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance2->id,
            'start_time' => '10:30:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/stamp_correction_request/list?tab=approved");

        $response->assertSee('2026/03/01');
        $response->assertSee('2026/03/02');
    }

    public function test_user_can_see_the_application_detail_from_applications_list()
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

        $this->actingAs($user)
            ->from('/attendance/detail/2026-03-01')
            ->post('/attendance/detail/2026-03-01', [
                'new_start_time' => '09:30',
                'new_end_time' => '18:00',
                'comment' => '電車遅延のため時間変更',
            ]);

        $attendance = Attendance::where('user_id', $user->id)->first();
        $workDate = $attendance->work_date->format('Y-m-d');

        $response = $this->actingAs($user)
        ->get('/stamp_correction_request/list');

        $response->assertSee("/attendance/detail/{$workDate}");

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$workDate}");

        $response->assertStatus(200);
    }
}
