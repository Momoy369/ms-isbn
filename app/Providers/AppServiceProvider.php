<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Models\Notification;
use App\Models\AuthorBookOrder;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

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

        Gate::before(function ($user, string $ability) {
            // Keep superadmin global bypass except author-only navigation abilities.
            if ($user->role === 'superadmin' && $ability !== 'menu-author') {
                return true;
            }

            return null;
        });

        $abilityRoles = [
            'menu-authenticated' => ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'author', 'finance', 'superadmin', 'customer', 'reader'],
            'menu-role-files' => ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'finance', 'superadmin'],
            'menu-backoffice-dashboard' => ['admin', 'editor', 'layouter', 'designer', 'isbn', 'owner', 'finance', 'superadmin'],
            'menu-author' => ['author'],
            'menu-production' => ['admin', 'editor', 'layouter', 'designer', 'owner', 'superadmin'],
            'menu-assignment-all' => ['admin', 'owner', 'finance', 'superadmin'],
            'menu-assignment-worker' => ['admin', 'editor', 'layouter', 'designer', 'owner', 'superadmin'],
            'menu-isbn' => ['admin', 'isbn', 'owner', 'superadmin'],
            'menu-user-management' => ['admin', 'isbn', 'superadmin'],
            'menu-finance' => ['admin', 'owner', 'finance', 'superadmin'],
            'menu-printing-workspace' => ['admin', 'owner', 'finance', 'editor', 'layouter', 'designer', 'superadmin'],
            'menu-ebook-workspace' => ['admin', 'owner', 'finance', 'editor', 'layouter', 'designer', 'superadmin'],
        ];

        foreach ($abilityRoles as $ability => $roles) {
            Gate::define($ability, fn($user) => in_array($user->role, $roles, true));
        }

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $pendingEbookCount = AuthorBookOrder::query()
                ->where('order_type', 'ebook_publication')
                ->whereIn('status', ['paid', 'ebook_revision_requested'])
                ->count();

            $event->menu->add([
                'key' => 'workspace-ebook-publishing-dynamic',
                'text' => 'Workspace Ebook Publishing',
                'route' => 'ebook.workspace.index',
                'icon' => 'fas fa-tablet-alt',
                'can' => 'menu-ebook-workspace',
                'label' => $pendingEbookCount > 0 ? (string) $pendingEbookCount : null,
                'label_color' => $pendingEbookCount > 0 ? 'warning' : null,
            ]);
        });

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
