<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Support both session auth (web) and JWT auth (api)
        $user = auth()->user() ?? auth('api')->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akses ditolak. Hanya admin yang diizinkan.'], 403);
            }

            return redirect()->route('login')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}
