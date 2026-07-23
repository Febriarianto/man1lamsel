<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            $settings = Schema::hasTable('settings')
                ? Setting::query()->pluck('value', 'key')->all()
                : [];
            $navbarMenus = Schema::hasTable('menus')
                ? Menu::query()->whereNull('parent_id')->where('active', true)
                    ->with(['childrenRecursive' => fn ($q) => $q->where('active', true)])
                    ->orderBy('sort_order')->orderBy('id')->get()
                : collect();
        } catch (\Throwable) {
            $settings = [];
            $navbarMenus = collect();
        }

        View::share('siteSettings', $settings);
        View::share('navbarMenus', $navbarMenus);
    }
}
