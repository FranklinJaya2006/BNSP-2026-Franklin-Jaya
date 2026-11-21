<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_gets_verification_message_instead_of_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'status' => 'pending',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Akun Anda masih menunggu verifikasi admin. Silakan coba lagi setelah disetujui.',
        ]);
        $this->assertGuest();
    }

    public function test_rejected_user_gets_rejection_message(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'status' => 'rejected',
            'rejection_notes' => 'Dokumen tidak jelas',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Akun Anda ditolak. Alasan: Dokumen tidak jelas',
        ]);
        $this->assertGuest();
    }

    public function test_approved_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'status' => 'approved',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('penyewa.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_still_uses_wrong_password_message(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'status' => 'approved',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }
}
