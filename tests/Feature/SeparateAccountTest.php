<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeparateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_guard_uses_its_own_account_table(): void
    {
        $renter = User::factory()->create(['email' => 'account@example.test', 'password' => 'renter-password']);
        $admin = Admin::create(['name' => 'Admin', 'email' => $renter->email, 'password' => 'admin-password']);
        $this->recordDecision($renter, $admin, 'approved');

        $this->assertTrue(Auth::guard('web')->validate(['email' => $renter->email, 'password' => 'renter-password']));
        $this->assertFalse(Auth::guard('web')->validate(['email' => $renter->email, 'password' => 'admin-password']));
        $this->assertTrue(Auth::guard('admin')->validate(['email' => $renter->email, 'password' => 'admin-password']));
        $this->assertFalse(Auth::guard('admin')->validate(['email' => $renter->email, 'password' => 'renter-password']));
    }

    public function test_a_renter_can_log_in_only_after_approval(): void
    {
        $renter = User::factory()->create(['password' => 'renter-password']);
        $admin = Admin::create(['name' => 'Admin', 'email' => 'reviewer@example.test', 'password' => 'unused']);
        $credentials = ['email' => $renter->email, 'password' => 'renter-password'];
        $guard = Auth::guard('web');

        $this->assertFalse($guard->attempt($credentials));

        $this->recordDecision($renter, $admin, 'rejected');
        $this->assertFalse($guard->attempt($credentials));

        $this->recordDecision($renter, $admin, 'approved');
        $this->assertTrue($guard->attempt($credentials));
        $this->assertSame($renter->id, $guard->id());

        $guard->logout();
        $this->recordDecision($renter, $admin, 'rejected');
        $this->assertFalse($guard->attempt($credentials));
    }

    public function test_a_renter_cannot_be_recorded_as_the_verifying_admin(): void
    {
        $renter = User::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('user_verifications')->insert([
            'user_id' => $renter->id, 'admin_id' => $renter->id, 'action' => 'approved', 'verified_at' => now(),
        ]);
    }

    private function recordDecision(User $renter, Admin $admin, string $action): void
    {
        DB::table('user_verifications')->insert([
            'user_id' => $renter->id, 'admin_id' => $admin->id, 'action' => $action,
            'verified_at' => '2026-11-21 09:00:00',
        ]);
    }
}
