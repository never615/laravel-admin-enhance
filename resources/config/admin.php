<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

return [

    /*
     * Laravel-admin name.
     */
    'name'          => env('APP_NAME', '深圳墨兔'),

    /*
     * Logo in admin panel header.
     */
    'logo'          => '深圳<b>墨兔</b>',

    /*
     * Mini-logo in admin panel header.
     */
    'logo-mini'     => '<b>墨</b>',

    /*
     * Route configuration.
     */
    'route'         => [
        'prefix'     => 'admin',
        'namespace'  => 'App\\Admin\\Controllers',
        'middleware' => ['web', 'admin'],
    ],

    /*
     * Laravel-admin install directory.
     */
    'directory'     => app_path('Admin'),


    /*
     * Laravel-admin html title.
     */
    'title'         => env('APP_NAME', '深圳墨兔'),


    /*
    * Use `https`.
    */
    'secure'        => env('SECURE',true),

    /*
     * Laravel-admin auth setting.
     */
    'auth'          => [
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
     * set default Exporter
     */
    'exporter'      => Mallto\Admin\Grid\Exporters\CsvExporter::class,

    /*
     * Laravel-admin upload setting.
     */
    'upload'        => [

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
     * Laravel-admin database setting.
     */
    'database'      => [

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
    'operation_log' => [
        'enable' => true,
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
     * @see https://adminlte.io/docs/2.4/layout
     */
    'skin'          => 'skin-blue-light',

    /*
    |---------------------------------------------------------|
    |LAYOUT OPTIONS | fixed                                   |
    |               | layout-boxed                            |
    |               | layout-top-nav                          |
    |               | sidebar-collapse                        |
    |               | sidebar-mini                            |
    |---------------------------------------------------------|
    */
    'layout'        => ['sidebar-mini'],


    /*
     * Version displayed in footer.
     */
    'version'       => env('APP_VERSION'),


    /*
     * Automatically generate a menu based on user-owned permissions.
     *
     * 在这种模式下,不需要根据用户角色创建菜单.只会有一份菜单,然后不同权限的人会根据自己的权限显示相应的菜单.
     *
     */
    'auto_menu'     => true,

    'admin_login' => '/admin/auth/login',


    /*
    * Settings for extensions.
    */
    'extensions'  => [
    ],

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
