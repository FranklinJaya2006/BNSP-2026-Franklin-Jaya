<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Auth\TenantLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** Urus daftar akun, login, dan logout web. */
class WebAuthController extends Controller
{
    public function __construct(private TenantLoginService $loginService) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $result = $this->loginService->validate(
            (string) $request->input('email'),
            (string) $request->input('password'),
        );

        if ($result->isSuccess() && $result->user !== null) {
            Auth::login($result->user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $result->user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('penyewa.dashboard');
        }

        if ($result->isBlocked()) {
            return back()
                ->withErrors([
                    'email' => $result->status->loginBlockedMessage(
                        $result->user?->rejection_notes,
                    ),
                ])
                ->withInput();
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'digits:16', 'unique:users,nik'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => UserRole::Penyewa->value,
            'status' => UserStatus::Pending->value,
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu verifikasi admin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
