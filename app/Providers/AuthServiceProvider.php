<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Admin: full quyền, không cần gán từng permission
        Gate::before(function ($user, $ability) {
            // Cho Admin full quyền: hỗ trợ cả Spatie role 'Admin' lẫn cột role_id = 1
            if ((method_exists($user, 'hasRole') && $user->hasRole('Admin')) || ($user->role_id ?? null) === 1) {
                return true;
            }
            return null;
        });

        // ===== Menu header composite gates =====
        // Hiển thị header SẢN PHẨM nếu có ÍT NHẤT 1 quyền liên quan
        Gate::define('menu.products', function ($user) {
            return $user->can('view products') || $user->can('create products') || $user->can('edit products') || $user->can('delete products')
                || $user->can('view categories') || $user->can('create categories') || $user->can('edit categories') || $user->can('delete categories')
                || $user->can('view promotions') || $user->can('create promotions') || $user->can('edit promotions') || $user->can('delete promotions')
                || $user->can('view shipping fees') || $user->can('create shipping fees') || $user->can('edit shipping fees') || $user->can('delete shipping fees')
                || $user->can('view orders') || $user->can('change status orders') || $user->can('print orders') || $user->can('view order detail');
        });

        // Hiển thị header KHÁCH HÀNG nếu có quyền khách hàng/đánh giá
        Gate::define('menu.customers', function ($user) {
            return $user->can('view customers') || $user->can('create customers') || $user->can('edit customers') || $user->can('delete customers') || $user->can('lock/unlock customers')
                || $user->can('view reviews') || $user->can('hide reviews') || $user->can('delete reviews');
        });

        // Hiển thị header THỐNG KÊ nếu có xem báo cáo
        Gate::define('menu.statistics', fn($user) => $user->can('view reports'));

        // Hiển thị header NHÂN VIÊN nếu có bất kỳ quyền nhân viên
        Gate::define('menu.staff', function ($user) {
            return $user->can('view staffs')
                || $user->can('create staffs')
                || $user->can('edit staffs')
                || $user->can('delete staffs')
                || $user->can('lock/unlock staffs');
        });

        // Hiển thị header HỆ THỐNG nếu có quyền hệ thống
        Gate::define('menu.system', function ($user) {
            return $user->can('manage settings')
                || $user->can('manage roles')
                || $user->can('manage permissions');
        });
    }
}
