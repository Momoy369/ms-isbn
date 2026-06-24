<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

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
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {

            $count = 0;

            if (auth()->check()) {

                $count = Notification::where(
                    'user_id',
                    auth()->id()
                )
                    ->where(
                        'is_read',
                        false
                    )
                    ->count();
            }

            $view->with(
                'unreadNotificationCount',
                $count
            );
        });
    }
}
