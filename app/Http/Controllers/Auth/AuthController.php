<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\TenantLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Urus akun, token login, dan profil lewat API. */
class AuthController extends Controller
{
    public function __construct(private TenantLoginService $loginService) {}

    /**
     * POST /auth/register
     * Penyewa mendaftar dengan NIK 16 digit, nama, telepon, email, password.
     * Akun dibuat dengan status PENDING — butuh approval admin sebelum bisa sewa.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik' => ['required', 'digits:16', 'unique:users,nik'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => UserRole::Penyewa->value,
            'status' => UserStatus::Pending->value,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil. Akun Anda sedang menunggu verifikasi admin.',
            'user' => $this->userResource($user),
        ], 201);
    }

    /**
     * POST /auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->loginService->validate(
            (string) $request->input('email'),
            (string) $request->input('password'),
        );

        if ($result->isSuccess() && $result->user !== null) {
            $token = auth('api')->login($result->user);

            return $this->tokenResponse($token);
        }

        if ($result->isBlocked()) {
            return response()->json([
                'message' => $result->status->loginBlockedMessage($result->user?->rejection_notes),
                'status' => $result->status->value,
            ], 403);
        }

        return response()->json(['message' => 'Email atau password salah.'], 401);
    }

    /**
     * POST /auth/logout
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * POST /auth/refresh
     */
    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(auth('api')->refresh());
    }

    /**
     * GET /auth/me
     */
    public function me(): JsonResponse
    {
        return response()->json(['user' => $this->userResource(auth('api')->user())]);
    }

    // --- Private helpers ---

    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $this->userResource(auth('api')->user()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return (new UserResource($user))->resolve();
    }
}
