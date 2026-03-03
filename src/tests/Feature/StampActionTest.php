<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class StampActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_work_from_off_duty_status()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 9, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/attendance')->assertSee('出勤');

        $this->post('/attendance', [
            'action' => 'start',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'start_time' => '09:00:00',
        ]);

        $this->get('/attendance')->assertSee('出勤中');
    }

    public function test_user_cannot_start_work_twice_in_same_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 18, 0));
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

        $this->actingAs($user)
            ->get('/attendance')
            ->assertDontSee('出勤');
    }

    public function test_start_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 9, 15));
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/attendance')
            ->assertSee('出勤');

        App::setLocale('ja');

        $this->post('/attendance', [
            'action' => 'start',
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/01(日)');
        $response->assertSee('09:15');
    }

    public function test_user_can_start_break_from_working_status()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
        ]);

        $this->actingAs($user)->get('/attendance')->assertSee('休憩入');

        $this->post('/attendance', [
            'action' => 'break_start',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => null,
        ]);

        $this->get('/attendance')
            ->assertSee('休憩中');
    }

    public function test_user_can_take_multiple_breaks_in_one_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'break_start',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 13, 0));

        $this->post('/attendance', [
            'action' => 'break_end',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);
        $this->get('/attendance')
            ->assertSee('休憩入');
    }

    public function test_break_end_button_returns_user_to_working_status()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'break_start',
        ]);
        $this->get('/attendance')
            ->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 13, 0));

        $this->post('/attendance', [
            'action' => 'break_end',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);
        $this->get('/attendance')
            ->assertSee('出勤中');
    }

    public function test_user_can_take_multiple_break_end_in_one_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'break_start',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 30));

        $this->post('/attendance', [
            'action' => 'break_end',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 13, 0));

        $this->post('/attendance', [
            'action' => 'break_start',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '12:30:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_time' => '13:00:00',
            'break_end_time' => null,
        ]);

        $this->get('/attendance')
            ->assertSee('休憩戻');
    }

    public function test_break_times_are_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 0));
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(3),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'break_start',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 12, 30));

        $this->post('/attendance', [
            'action' => 'break_end',
        ]);

        App::setLocale('ja');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/01(日)');
        $response->assertSee('0:30');
    }

    public function test_user_can_finish_work_from_working_status()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 17, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
        ]);

        $this->actingAs($user)->get('/attendance')->assertSee('退勤');

        $this->post('/attendance', [
            'action' => 'end'
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'end_time' => '17:00:00',
        ]);

        $this->get('/attendance')->assertSee('退勤済');
    }

    public function test_end_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 1, 9, 0));
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'start',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 3, 1, 17, 00));

        $this->post('/attendance', [
            'action' => 'end',
        ]);

        App::setLocale('ja');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('03/01(日)');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }
}
