<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use Illuminate\Pagination\Paginator;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Blade; 
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendGridTransport;

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
        // Force HTTPS on production so generated URLs (asset, route) use https
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Share categories for navbar/sidebar
        View::composer(['*'], function ($view) {
            try {
                $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);
            } catch (\Throwable $e) {
                $categories = collect();
            }
            // Compute cart count from DB for the authenticated user
            $cartCount = 0;
            $cartPreview = collect();
            $cartTotal = 0;
            try {
                if (auth()->check()) {
                    $cart = Cart::firstWhere('user_id', auth()->id());
                    if ($cart) {
                        // Show number of distinct products in cart (not total quantity), only active products
                        $activeItems = CartItem::where('cart_id', $cart->id)
                            ->with('product.product_images')
                            ->join('products', 'products.id', '=', 'cart_items.product_id')
                            ->where('products.status', 1)
                            ->select('cart_items.*')
                            ->get();
                        $cartCount = (int) $activeItems->count();
                        // Build preview and total
                        $cartPreview = $activeItems->map(function($ci){
                            $p = $ci->product;
                            if (!$p) return null;
                            return [
                                'id' => $p->id,
                                'name' => $p->name,
                                'qty' => (int)$ci->quantity,
                                'price' => (int)$p->price,
                                'subtotal' => (int)$p->price * (int)$ci->quantity,
                                'image' => optional($p->product_images[0] ?? null)->image_url,
                            ];
                        })->filter();
                        $cartTotal = (int) $cartPreview->sum('subtotal');
                    }
                }
            } catch (\Throwable $e) {
                $cartCount = 0;
                $cartPreview = collect();
                $cartTotal = 0;
            }
            $view->with('sharedCategories', $categories)
                ->with('sharedCartCount', $cartCount)
                ->with('sharedCartPreview', $cartPreview)
                ->with('sharedCartTotal', $cartTotal);
        });

        // Use Bootstrap 5 pagination templates
        Paginator::useBootstrapFive();

        // SendGrid mail driver
        Mail::extend('sendgrid', function (array $config = [], string $name = null) {
            $apiKey = $config['api_key'] ?? config('services.sendgrid.api_key');
            return new SendGridTransport($apiKey);
        });
    }
}
