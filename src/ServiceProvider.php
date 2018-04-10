<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        'Mallto\Admin\Console\InstallCommand',
        'Mallto\Admin\Console\UpdateCommand',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'adminE.auto_permission' => \Mallto\Admin\Middleware\AutoPermissionMiddleware::class,
        'adminE.log' => \Mallto\Admin\Middleware\OperationLog::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
    ];


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../resources/config' => config_path()],
                'laravel-admin-enhance-config');
            $this->publishes([__DIR__ . '/../resources/assets' => public_path('vendor/laravel-adminE')],
                'laravel-admin-enhance-assets');


            //发布view覆盖laravel-admin的view
            $this->publishes([__DIR__ . '/../resources/admin/views' => resource_path('views/vendor/admin')],
                'laravel-admin-enhance-views');
            //发布assets覆盖laravel-admin的assets
            $this->publishes([__DIR__ . '/../resources/admin/assets' => public_path('vendor/laravel-admin')],
                'laravel-admin-enhance-assets');


        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'adminE');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouteMiddleware();

        $this->commands($this->commands);
    }


    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }


}
