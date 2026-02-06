<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
		commands: __DIR__.'/../routes/console.php',
        health: '/up',
		apiPrefix: 'order_api/api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/auth/*',
            'api/admin/*',
        ]);
		$middleware->alias([
			'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
			'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
			
			'role' => \App\Http\Middleware\CheckRole::class,
		]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json([
					'success' => false,
					'message' => 'غير مصرح بالوصول',
					'error' => $e->getMessage()
				], 403);
			}
		});
    })->create();
