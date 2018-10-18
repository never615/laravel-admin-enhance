<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin name
    |--------------------------------------------------------------------------
    |
    | This value is the name of laravel-admin, This setting is displayed on the
    | login page.
    |
    */
    'name'                   => env('APP_NAME', '深圳墨兔'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages. You can also set it as an image by using a
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo'                   => '深圳<b>墨兔</b>',

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin mini logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages when the sidebar menu is collapsed. You can
    | also set it as an image by using a `img` tag, eg
    | '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo-mini'              => '<b>墨</b>',

    /*
     |--------------------------------------------------------------------------
     | Laravel-admin route settings
     |--------------------------------------------------------------------------
     |
     | The routing configuration of the admin page, including the path prefix,
     | the controller namespace, and the default middleware. If you want to
     | access through the root path, just set the prefix to empty string.
     |
     */
    'route'                  => [
        'prefix'     => 'admin',
        'namespace'  => 'App\\Admin\\Controllers',
        'middleware' => ['web', 'adminE_base'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin install directory
    |--------------------------------------------------------------------------
    |
    | The installation directory of the controller and routing configuration
    | files of the administration page. The default is `app/Admin`, which must
    | be set before running `artisan admin::install` to take effect.
    |
    */
    'directory'              => app_path('Admin'),


    /*
    |--------------------------------------------------------------------------
    | Laravel-admin html title
    |--------------------------------------------------------------------------
    |
    | Html title for all pages.
    |
    */
    'title'                  => env('APP_NAME', '深圳墨兔'),


    /*
    |--------------------------------------------------------------------------
    | Access via `https`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via https, set it to `true`.
    |
    */
    'secure'                 => env('SECURE', true),
    'https'                  => env('SECURE', true),

    /*
    * set default Exporter
    */
    'exporter'               => Mallto\Admin\Grid\Exporters\CsvExporter::class,

    /*
     * Laravel-admin auth setting.
     */
    'auth'                   => [
        'controller' => App\Admin\Controllers\AuthController::class,

        'guards'    => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],
        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => Mallto\Admin\Data\Administrator::class,
            ],
        ],
    ],


    /*
     * Laravel-admin upload setting.
     */
    'upload'                 => [

        'disk' => 'qiniu',

        'private_disk' => 'qiniu_private',

        'directory' => [
            'image' => 'image',
            'file'  => 'file',
            'video' => 'video',
        ],

//        'host' => env("FILE_URL_PREFIX"),
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin auth setting
    |--------------------------------------------------------------------------
    |
    | Authentication settings for all admin pages. Include an authentication
    | guard and a user provider setting of authentication driver.
    |
    */
    'database'               => [

        // Database connection for following tables.
        'connection'             => 'pgsql',

        // User tables and model.
        'users_table'            => 'admin_users',
        'users_model'            => Mallto\Admin\Data\Administrator::class,


        // Role table and model.
        'roles_table'            => 'admin_roles',
        'roles_model'            => \Mallto\Admin\Data\Role::class,

        // Permission table and model.
        'permissions_table'      => 'admin_permissions',
        'permissions_model'      => \Mallto\Admin\Data\Permission::class,

        // Menu table and model.
        'menu_table'             => 'admin_menu',
        'menu_model'             => \Mallto\Admin\Data\Menu::class,

        // Pivot table for table above.
        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],


    /*
     * By setting this option to open or close operation log in laravel-admin.
     */
    'operation_log'          => [
        'enable' => true,


        /*
         * Only logging allowed methods in the list
         */
        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'],

        /*
         * Routes that will not log to database.
         *
         * All method to path like: admin/auth/logs
         * or specific method to path like: get:admin/auth/logs
         */
        'except' => [
            'admin/auth/logs*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin map field provider
    |--------------------------------------------------------------------------
    |
    | Supported: "tencent", "google", "yandex".
    |
    */
    'map_provider' => 'google',

    /*
    |--------------------------------------------------------------------------
    | Application Skin
    |--------------------------------------------------------------------------
    |
    | This value is the skin of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported:
    |    "skin-blue", "skin-blue-light", "skin-yellow", "skin-yellow-light",
    |    "skin-green", "skin-green-light", "skin-purple", "skin-purple-light",
    |    "skin-red", "skin-red-light", "skin-black", "skin-black-light".
    |
    */
    'skin' => 'skin-blue-light',

    /*
    |--------------------------------------------------------------------------
    | Application layout
    |--------------------------------------------------------------------------
    |
    | This value is the layout of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse",
    | "sidebar-mini".
    |
    */
    'layout' => ['sidebar-mini', 'sidebar-collapse'],

    /*
    |--------------------------------------------------------------------------
    | Login page background image
    |--------------------------------------------------------------------------
    |
    | This value is used to set the background image of login page.
    |
    */
    'login_background_image' => '',

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This version number set will appear in the page footer.
    |
    */
    'version'                => env('APP_VERSION'),


    /*
   |--------------------------------------------------------------------------
   | Show version at footer
   |--------------------------------------------------------------------------
   |
   | Whether to display the version number of laravel-admim at the footer of
   | each page
   |
   */
    'show_version' => true,

    /*
    |--------------------------------------------------------------------------
    | Show environment at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the environment at the footer of each page
    |
    */
    'show_environment' => true,

    /*
    |--------------------------------------------------------------------------
    | Menu bind to permission
    |--------------------------------------------------------------------------
    |
    | whether enable menu bind to a permission
    */
    'menu_bind_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable default breadcrumb
    |--------------------------------------------------------------------------
    |
    | Whether enable default breadcrumb for every page content.
    */
    'enable_default_breadcrumb' => true,

    /*
    |--------------------------------------------------------------------------
    | Extension Directory
    |--------------------------------------------------------------------------
    |
    | When you use command `php artisan admin:extend` to generate extensions,
    | the extension files will be generated in this directory.
    */
    'extension_dir' => app_path('Admin/Extensions'),

    /*
    |--------------------------------------------------------------------------
    | Settings for extensions.
    |--------------------------------------------------------------------------
    |
    | You can find all available extensions here
    | https://github.com/laravel-admin-extensions.
    |
    */
    'extensions' => [

    ],


    /*
     * Automatically generate a menu based on user-owned permissions.
     *
     * 在这种模式下,不需要根据用户角色创建菜单.只会有一份菜单,然后不同权限的人会根据自己的权限显示相应的菜单.
     *
     */
    'auto_menu'              => true,

    'admin_login' => '/admin/auth/login',



    /*
     * WangEditor
     *
     * 菜单按钮全局配置
     */
    'editor_menu' => [
        'bold',
        'underline',
        'italic',
        'strikethrough',
        'eraser',
        'forecolor',
        'bgcolor',
        '|',
        'quote',
        'fontfamily',
        'fontsize',
        'head',
        'unorderlist',
        'orderlist',
        'alignleft',
        'aligncenter',
        'alignright',
        '|',
        'link',
        'unlink',
        'table',
        '|',
        'img',
        'video',
        '|',
        'undo',
        'redo',
        'fullscreen',
    ],


    /*
     * 角色名字
     */
    'roles'       => [
        'owner' => 'owner',
        'admin' => 'admin',
    ],
];
