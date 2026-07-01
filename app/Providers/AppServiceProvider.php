<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Models\Notification;
use App\Models\AuthorBookOrder;
use App\Models\AuthorUpgradeRequest;
use App\Models\StorePackageConsultation;
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
            // Keep superadmin global bypass except role-specific navigation abilities.
            if (
                $user->role === 'superadmin'
                && !in_array($ability, ['menu-author', 'menu-customer-dashboard'], true)
            ) {
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
            'menu-customer-dashboard' => ['customer', 'reader'],
            'menu-author-upgrade-review' => ['admin', 'isbn', 'superadmin'],
        ];

        foreach ($abilityRoles as $ability => $roles) {
            Gate::define($ability, fn($user) => in_array($user->role, $roles, true));
        }

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $pendingOperationsCount = AuthorBookOrder::query()
                ->whereIn('order_type', ['reprint', 'ebook_publication'])
                ->whereIn('status', [
                    'paid',
                    'revision_requested',
                    'ebook_revision_requested',
                    'printing',
                    'processing',
                    'ebook_publishing',
                    'shipping',
                ])
                ->count();

            $pendingEbookCount = AuthorBookOrder::query()
                ->where('order_type', 'ebook_publication')
                ->whereIn('status', ['paid', 'ebook_revision_requested'])
                ->count();

            $pendingUpgradeCount = AuthorUpgradeRequest::query()
                ->where('status', 'pending')
                ->count();

            $pendingPackageLeadCount = StorePackageConsultation::query()
                ->where('status', 'pending')
                ->count();

            $event->menu->add([
                'key' => 'customer-dashboard-dynamic',
                'text' => 'Dashboard Customer',
                'route' => 'customer.dashboard',
                'icon' => 'fas fa-shopping-bag',
                'can' => 'menu-customer-dashboard',
            ]);

            $event->menu->add([
                'key' => 'customer-storefront-dynamic',
                'text' => 'Kembali ke Storefront',
                'route' => 'store.index',
                'icon' => 'fas fa-store',
                'can' => 'menu-customer-dashboard',
            ]);

            $event->menu->add([
                'key' => 'customer-orders-dynamic',
                'text' => 'Order & Invoice Store',
                'route' => 'customer.orders.index',
                'icon' => 'fas fa-file-invoice',
                'can' => 'menu-customer-dashboard',
            ]);

            $event->menu->add([
                'key' => 'customer-ebook-library-dynamic',
                'text' => 'Library Ebook',
                'route' => 'customer.ebooks.index',
                'icon' => 'fas fa-book-open',
                'can' => 'menu-customer-dashboard',
            ]);

            $event->menu->add([
                'key' => 'author-storefront-group-dynamic',
                'text' => 'Storefront & Customer',
                'icon' => 'fas fa-store',
                'can' => 'menu-author',
                'label' => 'AUTH',
                'label_color' => 'info',
                'submenu' => [
                    [
                        'text' => 'Dashboard Author',
                        'route' => 'author.dashboard',
                        'icon' => 'fas fa-user-edit',
                    ],
                    [
                        'text' => 'Dashboard Customer',
                        'route' => 'customer.dashboard',
                        'icon' => 'fas fa-shopping-bag',
                    ],
                    [
                        'text' => 'Order & Invoice Store',
                        'route' => 'customer.orders.index',
                        'icon' => 'fas fa-file-invoice',
                    ],
                    [
                        'text' => 'Kembali ke Storefront',
                        'route' => 'store.index',
                        'icon' => 'fas fa-store-alt',
                    ],
                ],
            ]);

            $event->menu->add([
                'key' => 'author-upgrade-review-dynamic',
                'text' => 'Review Upgrade Author',
                'route' => 'admin.author-upgrades.index',
                'icon' => 'fas fa-user-check',
                'can' => 'menu-author-upgrade-review',
                'label' => $pendingUpgradeCount > 0 ? (string) $pendingUpgradeCount : null,
                'label_color' => $pendingUpgradeCount > 0 ? 'warning' : null,
            ]);

            $event->menu->add([
                'key' => 'operations-dashboard-dynamic',
                'text' => 'Dashboard Operasional',
                'route' => 'production.dashboard',
                'icon' => 'fas fa-clipboard-list',
                'can' => 'menu-production',
                'label' => $pendingOperationsCount > 0 ? (string) $pendingOperationsCount : null,
                'label_color' => $pendingOperationsCount > 0 ? 'danger' : null,
            ]);

            $event->menu->add([
                'key' => 'finance-storefront-group-dynamic',
                'text' => 'Storefront',
                'icon' => 'fas fa-ticket-alt',
                'can' => 'menu-finance',
                'submenu' => [
                    [
                        'text' => 'Catalog Storefront',
                        'route' => 'finance.store.catalog.index',
                        'icon' => 'fas fa-book',
                    ],
                    [
                        'text' => 'Order Storefront',
                        'route' => 'finance.store.orders.index',
                        'icon' => 'fas fa-clipboard-list',
                    ],
                    [
                        'text' => 'Lead Paket Penerbitan',
                        'route' => 'finance.store.package-consultations.index',
                        'icon' => 'fas fa-user-tie',
                        'label' => $pendingPackageLeadCount > 0 ? (string) $pendingPackageLeadCount : null,
                        'label_color' => $pendingPackageLeadCount > 0 ? 'warning' : null,
                    ],
                    [
                        'text' => 'Voucher Storefront',
                        'route' => 'finance.store.vouchers.index',
                        'icon' => 'fas fa-ticket-alt',
                    ],
                    [
                        'text' => 'Export Penjualan',
                        'route' => 'finance.export.store-sales',
                        'icon' => 'fas fa-file-csv',
                    ],
                    [
                        'text' => 'Lihat Store Publik',
                        'route' => 'store.index',
                        'icon' => 'fas fa-external-link-alt',
                    ],
                ],
            ]);

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
