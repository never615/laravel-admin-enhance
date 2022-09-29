<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Domain\User\AdminUserUsecase;
use Mallto\Admin\Domain\User\AdminUserUsecaseImpl;
use Mallto\Admin\Listeners\CreateAdminRole;
use Mallto\Admin\Listeners\Events\SubjectSaved;
use Mallto\Admin\Listeners\SubjectCacheClear;
use Mallto\Admin\Middleware\Pjax;
use Mallto\Admin\Observers\SubjectConfigObserver;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * @var array
     */
    protected $commands = [
        'Mallto\Admin\Console\InstallCommand',
        'Mallto\Admin\Console\UpdateCommand',
        'Mallto\Admin\Console\PathGeneratorCommand',
        'Mallto\Admin\Console\MenuCommand',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin.auth'             => \Mallto\Admin\Middleware\Authenticate::class,
        'adminE.auto_permission' => \Mallto\Admin\Middleware\AutoPermissionMiddleware::class,
        'adminE.log'             => \Mallto\Admin\Middleware\OperationLog::class,
        'adminE.pjax'            => Pjax::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'adminE'      => [
            'admin.auth',
            'adminE.pjax',
            'admin.bootstrap',
            'adminE.auto_permission',
            'adminE.log',
        ],
        'adminE_base' => [
            'admin.auth',
            'adminE.pjax',
            'admin.bootstrap',
            'adminE.log',
        ],
    ];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SubjectSaved::class => [
            CreateAdminRole::class,
            SubjectCacheClear::class,
        ],
    ];


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([ __DIR__ . '/../resources/config' => config_path() ],
                'laravel-admin-enhance-config');

            $this->publishes([ __DIR__ . '/../resources/assets' => public_path('vendor/laravel-adminE') ],
                'laravel-admin-enhance-assets');

            //$this->publishes([ __DIR__ . '/../resources/file' => public_path('vendor/file') ],
            //    'laravel-admin-enhance-assets');

            //发布view覆盖laravel-admin的view
            $this->publishes([ __DIR__ . '/../resources/admin/views' => resource_path('views/vendor/admin') ],
                'laravel-admin-enhance-views');
            //发布assets覆盖laravel-admin的assets
//            $this->publishes([__DIR__.'/../resources/admin/assets' => public_path('vendor/laravel-admin')],
//                'laravel-admin-enhance-assets');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'adminE');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->customMorphMap();

        //$this->adminBootstrap();
        $this->registerEventListeners();
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

        $this->app->bind(
            AdminUserUsecase::class,
            AdminUserUsecaseImpl::class
        );

        if ( ! Type::hasType('double')) {
            try {
                Type::addType('double', FloatType::class);
            } catch (DBALException $e) {
            }
        }

        //Admin::booted(function () {
        //    \Mallto\Admin\Facades\AdminE::quickAccess();
        //});
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


    protected function customMorphMap()
    {
        Relation::morphMap([
            'subject' => Subject::class,
        ]);
    }


    protected function registerEventListeners()
    {
        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        SubjectConfig::observe(SubjectConfigObserver::class);
    }


    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }

}
