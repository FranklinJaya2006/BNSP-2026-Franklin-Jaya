<?php

use App\Http\Middleware\AdminOnly;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Override RedirectIfAuthenticated agar redirect ke dashboard sesuai role
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();
            if ($user && $user->isAdmin()) {
                return route('admin.dashboard');
            }

            return route('penyewa.dashboard');
        });

        $middleware->alias([
            'admin' => AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

$app->booting(function (Application $app): void {
    Auth::provider('approved-tenants', function (Application $app, array $config): EloquentUserProvider {
        return (new EloquentUserProvider($app['hash'], $config['model']))
            ->withQuery(fn (Builder $query): Builder => $query->approved());
    });
});

return $app;
