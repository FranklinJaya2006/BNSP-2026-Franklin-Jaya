<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectUserRequest;
use App\Models\User;
use App\Services\UserVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserVerificationService $verifications) {}

    public function index(Request $request): View
    {
        $query = User::tenants()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(int $id): View
    {
        $user = User::tenants()->with('approvedBy:id,name')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function approve(int $id): RedirectResponse
    {
        $user = User::tenants()->findOrFail($id);
        $this->verifications->approve($user, auth()->user());

        return back()->with('success', "Akun {$user->name} berhasil disetujui.");
    }

    public function reject(RejectUserRequest $request, int $id): RedirectResponse
    {
        $user = User::tenants()->findOrFail($id);
        $this->verifications->reject($user, auth()->user(), $request->input('notes'));

        return back()->with('success', "Akun {$user->name} ditolak.");
    }
}
