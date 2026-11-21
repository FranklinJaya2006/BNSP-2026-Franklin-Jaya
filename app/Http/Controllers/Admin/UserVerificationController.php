<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
    public function __construct(private UserVerificationService $verifications) {}

    /**
     * GET /admin/users
     * Daftar semua penyewa (bisa filter by status).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::tenants()
            ->select('id', 'nik', 'name', 'email', 'phone', 'status', 'approved_by', 'approved_at', 'created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        $users->getCollection()->transform(
            fn (User $user): User => $user->setAttribute('nik', $user->maskedNik()),
        );

        return response()->json($users);
    }

    /**
     * POST /admin/users/{id}/approve
     * Admin menyetujui pendaftaran penyewa.
     */
    public function approve(int $id): JsonResponse
    {
        $user = User::tenants()->findOrFail($id);

        if ($user->status === UserStatus::Approved->value) {
            return response()->json(['message' => 'Akun penyewa sudah disetujui sebelumnya.'], 409);
        }

        $this->verifications->approve($user, auth('api')->user());

        return response()->json([
            'message' => 'Akun penyewa berhasil disetujui.',
            'user' => $this->userResource($user),
        ]);
    }

    /**
     * POST /admin/users/{id}/reject
     * Admin menolak/menangguhkan akun penyewa.
     */
    public function reject(RejectUserRequest $request, int $id): JsonResponse
    {
        $user = User::tenants()->findOrFail($id);

        if ($user->status === UserStatus::Rejected->value) {
            return response()->json(['message' => 'Akun penyewa sudah ditolak sebelumnya.'], 409);
        }

        $this->verifications->reject($user, auth('api')->user(), $request->input('notes'));

        return response()->json([
            'message' => 'Akun penyewa ditolak.',
            'user' => $this->userResource($user),
        ]);
    }

    /**
     * GET /admin/users/{id}
     * Detail satu penyewa.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::tenants()
            ->with('approvedBy:id,name')
            ->findOrFail($id);

        $resource = $this->userResource($user);
        $resource['approved_by_name'] = $user->approvedBy?->name;
        $resource['rejection_notes'] = $user->rejection_notes;
        $resource['created_at'] = $user->created_at;

        return response()->json(['user' => $resource]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return (new UserResource($user))->resolve();
    }
}
