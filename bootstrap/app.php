<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['checkAdmin' => AdminMiddleware::class]);
        // Trust all proxies (Railway) so X-Forwarded-* headers are honored for HTTPS detection
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error pages for admin routes
        $exceptions->render(function (Throwable $e, $request) {
            // Check if request is for admin routes
            // Method 1: Check route name patterns
            $routeName = $request->route() ? $request->route()->getName() : '';
            $isAdminRoute = str_starts_with($routeName, 'dashboard') || 
                           str_starts_with($routeName, 'orders.') || 
                           str_starts_with($routeName, 'analytics') || 
                           str_starts_with($routeName, 'settings') || 
                           str_starts_with($routeName, 'customers.') || 
                           str_starts_with($routeName, 'products.');
            
            // Method 2: Check URL patterns as fallback
            if (!$isAdminRoute) {
                $adminPaths = ['dashboard', 'orders', 'analytics', 'settings', 'customers', 'products', 'data'];
                foreach ($adminPaths as $path) {
                    if ($request->is($path) || $request->is($path . '/*')) {
                        $isAdminRoute = true;
                        break;
                    }
                }
            }
            
            if ($isAdminRoute) {
                // Handle 404 errors
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->view('errors.admin.404', [], 404);
                }
                
                // Handle 403 errors
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
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
