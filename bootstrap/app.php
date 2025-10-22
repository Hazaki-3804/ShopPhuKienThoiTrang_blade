<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'checkAdmin' => AdminMiddleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);        // Trust all proxies (Railway) so X-Forwarded-* headers are honored for HTTPS detection
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error pages for admin routes
        $exceptions->render(function (Throwable $e, $request) {
            // Check if request is for admin routes
            $routeName = $request->route() ? $request->route()->getName() : '';

            // Prefer route name detection
            $isAdminRoute = false;
            if ($routeName) {
                $isAdminRoute = str_starts_with($routeName, 'admin.')
                    || str_starts_with($routeName, 'dashboard')
                    || str_starts_with($routeName, 'settings');
            }

            // Fallback: URL patterns
            if (!$isAdminRoute) {
                if ($request->is('admin') || $request->is('admin/*')) {
                    $isAdminRoute = true;
                } else {
                    $adminPaths = ['dashboard', 'orders', 'analytics', 'settings', 'customers', 'products', 'data'];
                    foreach ($adminPaths as $path) {
                        if ($request->is($path) || $request->is($path . '/*')) {
                            $isAdminRoute = true;
                            break;
                        }
                    }
                }
            }
            
            if ($isAdminRoute) {
                // Handle 404 errors
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->view('errors.admin.404', [], 404);
                }

                // Handle 403 errors (AccessDenied, AuthorizationException, Spatie UnauthorizedException)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
                    || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                    || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                    return response()->view('errors.admin.403', [], 403);
                }
                
                // Handle 500 errors and other server errors
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() >= 500) {
                    return response()->view('errors.admin.500', ['exception' => $e], 500);
                }
                
                // Handle general server errors (non-HTTP exceptions)
                if (!($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException)) {
                    return response()->view('errors.admin.500', ['exception' => $e], 500);
                }
            }
            
            // Return null to use default error handling for non-admin routes
            return null;
        });
    })->create();
