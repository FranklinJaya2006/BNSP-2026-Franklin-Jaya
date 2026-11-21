<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Override bawaan Laravel agar redirect ke dashboard yang sesuai role.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect ke dashboard sesuai role
                $redirect = $user->isAdmin()
                    ? route('admin.dashboard')
                    : route('penyewa.dashboard');

                return redirect($redirect);
            }
        }

        return $next($request);
    }
}
