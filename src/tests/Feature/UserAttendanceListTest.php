<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

use App\Models\Attendance;
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

    public function test_user_can_see_all_attendances()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        
        
    }
}
