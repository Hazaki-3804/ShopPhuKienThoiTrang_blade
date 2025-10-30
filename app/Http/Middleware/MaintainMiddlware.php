<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class MaintainMiddlware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Loại trừ admin routes và auth routes
        $excludedPaths = [
            'admin/*',
            'login',
            'logout',
        ];

        foreach ($excludedPaths as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Loại trừ admin users
        if (auth()->check() && auth()->user()->hasRole(['Admin', "Nhân viên"])) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        $siteStatus = Setting::get('site_status');
        if ($siteStatus === 'maintenance') {
            return response()->view('errors.maintain', [], 503);
        }

        return $next($request);
    }
}
