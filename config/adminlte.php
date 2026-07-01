<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'MS ISBN',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>MS</b> ISBN',
    'logo_img' => 'logowide2.png',
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'MS ISBN',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'logowide.png',
            'alt' => 'MS ISBN',
            'class' => '',
            'width' => 220,
            'height' => 64,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'logowide2.png',
            'alt' => 'MS ISBN',
            'effect' => 'animation__shake',
            'width' => 220,
            'height' => 64,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
            'can' => 'menu-authenticated',
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
            'can' => 'menu-authenticated',
        ],
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
            'can' => 'menu-authenticated',
        ],
        [
            'text' => 'Dasbor',
            'route' => 'dashboard',
            'icon' => 'fas fa-home',
            'can' => 'menu-backoffice-dashboard',
        ],
        [
            'header' => 'Operasional Backoffice',
            'can' => 'menu-backoffice-dashboard',
        ],
        [
            'text' => 'Dasbor Penulis',
            'route' => 'author.dashboard',
            'icon' => 'fas fa-feather-alt',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Produksi Buku',
            'icon' => 'fas fa-industry',
            'can' => 'menu-backoffice-dashboard',
            'submenu' => [
                [
                    'text' => 'Naskah',
                    'route' => 'books.index',
                    'icon' => 'fas fa-book',
                    'can' => 'menu-backoffice-dashboard',
                ],
                [
                    'text' => 'Paket Penerbitan',
                    'route' => 'publishing-packages.index',
                    'icon' => 'fas fa-cubes',
                    'can' => 'menu-isbn',
                ],
                [
                    'text' => 'Antrian ISBN',
                    'route' => 'isbn.queue',
                    'icon' => 'fas fa-barcode',
                    'can' => 'menu-isbn',
                ],
                [
                    'text' => 'Assignment',
                    'url' => '/assignments',
                    'icon' => 'fas fa-tasks',
                    'can' => 'menu-assignment-all',
                ],
                [
                    'text' => 'Assignment Saya',
                    'url' => '/my-assignments',
                    'icon' => 'fas fa-user-check',
                    'can' => 'menu-assignment-worker',
                ],
                [
                    'text' => 'Papan Pribadi',
                    'route' => 'personal-board.index',
                    'icon' => 'fas fa-columns',
                    'can' => 'menu-personal-board',
                ],
                [
                    'text' => 'Production',
                    'url' => 'production',
                    'icon' => 'fas fa-industry',
                    'can' => 'menu-production',
                ],
                [
                    'text' => 'Production Timeline',
                    'route' => 'production.timeline',
                    'icon' => 'fas fa-stream',
                    'can' => 'menu-production',
                ],
                [
                    'text' => 'Layout Generator',
                    'url' => 'layout-generator',
                    'icon' => 'fas fa-book-open',
                    'can' => 'menu-production',
                ],
            ],
        ],
        [
            'header' => 'Keuangan & Penjualan',
            'can' => 'menu-finance',
        ],
        [
            'text' => 'Users',
            'url' => '/users',
            'icon' => 'fas fa-users',
            'can' => 'menu-user-management',
        ],
        [
            'text' => 'Modul Finance',
            'icon' => 'fas fa-wallet',
            'can' => 'menu-finance',
            'submenu' => [
                [
                    'text' => 'Finance Invoice',
                    'route' => 'finance.invoices.index',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can' => 'menu-finance',
                ],
                [
                    'text' => 'Harga Cetak',
                    'route' => 'print-prices.index',
                    'icon' => 'fas fa-print',
                    'can' => 'menu-finance',
                ],
                [
                    'text' => 'Penjualan Eksternal',
                    'route' => 'external-sales.index',
                    'icon' => 'fas fa-store',
                    'can' => 'menu-finance',
                ],
                [
                    'text' => 'Katalog Buku Legacy',
                    'route' => 'legacy-books.index',
                    'icon' => 'fas fa-book-open',
                    'can' => 'menu-finance',
                ],
                [
                    'text' => 'Royalti & Pencairan',
                    'route' => 'finance.royalties.index',
                    'icon' => 'fas fa-hand-holding-usd',
                    'can' => 'menu-finance',
                ],
                [
                    'text' => 'Layanan Tambahan',
                    'route' => 'additional-services.index',
                    'icon' => 'fas fa-concierge-bell',
                    'can' => 'menu-finance',
                ],
            ],
        ],
        [
            'text' => 'Workspace Percetakan',
            'route' => 'printing.workspace.index',
            'icon' => 'fas fa-print',
            'can' => 'menu-printing-workspace',
        ],
        [
            'header' => 'Portal Penulis',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Invoice Penulis',
            'route' => 'author.invoices.index',
            'icon' => 'fas fa-money-check-alt',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Claim Buku Penulis',
            'route' => 'author.claims.index',
            'icon' => 'fas fa-id-card',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Order Penulis',
            'route' => 'author.orders.index',
            'icon' => 'fas fa-shopping-cart',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Royalti Penulis',
            'route' => 'author.royalties.index',
            'icon' => 'fas fa-coins',
            'can' => 'menu-author',
        ],
        [
            'text' => 'Ruang File Role',
            'route' => 'role-files.index',
            'icon' => 'fas fa-folder-open',
            'can' => 'menu-role-files',
        ],
        [
            'text' => 'Notifikasi',
            'route' => 'notifications.index',
            'icon' => 'fas fa-bell',
            'topnav_right' => true,
            'can' => 'menu-authenticated',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'menu_filters_data' => [],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
