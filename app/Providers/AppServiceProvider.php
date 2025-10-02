<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        Blade::componentNamespace('App\\View\\Components\\Admin\\Components', 'admin');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share categories for navbar/sidebar
        View::composer(['*'], function ($view) {
            try {
                $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);
            } catch (\Throwable $e) {
                $categories = collect();
            }
            $cart = session('cart', []);
            $cartCount = 0;
            foreach ($cart as $line) {
                $cartCount += (int)($line['qty'] ?? 0);
            }
            $view->with('sharedCategories', $categories)->with('sharedCartCount', $cartCount);
        });

        // Use Bootstrap 5 pagination templates
        Paginator::useBootstrapFive();
    }
}
