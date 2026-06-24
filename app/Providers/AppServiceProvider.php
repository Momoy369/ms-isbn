<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
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

        Gate::before(function ($user) {
            return $user->role === 'superadmin' ? true : null;
        });

        $abilityRoles = [
            'menu-authenticated' => ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'author', 'finance', 'superadmin'],
            'menu-backoffice-dashboard' => ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'finance', 'superadmin'],
            'menu-author' => ['author'],
            'menu-production' => ['admin', 'editor', 'layouter', 'designer', 'owner', 'superadmin'],
            'menu-assignment-all' => ['admin', 'owner', 'finance', 'superadmin'],
            'menu-assignment-worker' => ['admin', 'editor', 'layouter', 'designer', 'owner', 'superadmin'],
            'menu-isbn' => ['admin', 'isbn', 'owner', 'superadmin'],
            'menu-user-management' => ['admin', 'isbn', 'superadmin'],
            'menu-finance' => ['admin', 'owner', 'finance', 'superadmin'],
        ];

        foreach ($abilityRoles as $ability => $roles) {
            Gate::define($ability, fn($user) => in_array($user->role, $roles, true));
        }

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
