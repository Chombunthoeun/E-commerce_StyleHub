<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $view->with('sidebarPendingOrders', Order::where('status', 'Pending')->count());
        });

        View::composer('layouts.app', function ($view) {
            $view->with('discountedProducts', Product::where('is_active', true)
                ->where('discount_percent', '>', 0)
                ->orderByDesc('discount_percent')
                ->limit(12)
                ->get());
        });
    }
}
