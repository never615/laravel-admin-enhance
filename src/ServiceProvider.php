<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Domain\User\AdminUserUsecase;
use Mallto\Admin\Domain\User\AdminUserUsecaseImpl;
use Mallto\Admin\Middleware\Pjax;

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

        $this->adminBootstrap();

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

        Admin::booted(function () {
            \Mallto\Admin\Facades\AdminE::quickAccess();
        });
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


    protected function adminBootstrap()
    {
        Admin::booting(function () {
            //表单文件上传控件:支持直传文件到七牛,目前支持单文件
            \Encore\Admin\Form::extend('qiniuFile', \Mallto\Admin\Form\Field\QiniuFile::class);
            //表单文件上传控件:支持直传文件到七牛,目前支持多文件
            \Encore\Admin\Form::extend('qiniuMultipleFile',
                \Mallto\Admin\Form\Field\QiniuMultipleFile::class);
            //表单按钮控件:laravel-admin的button有bug,此为修复版本
            \Encore\Admin\Form::extend('buttonE', \Mallto\Admin\Form\Field\Button::class);
            //表单文件上传控件:支持上传文件到七牛的私有空间
            \Encore\Admin\Form::extend('filePrivate', \Mallto\Admin\Form\Field\FilePrivate::class);
            //表单select控件,支持ajaxLoad,即:select联动支持分页加载
            \Encore\Admin\Form::extend('selectE', \Mallto\Admin\Form\Field\Select::class);
            //表单multipleSelect,支持ajaxLoad,即:select联动支持分页加载
            \Encore\Admin\Form::extend('multipleSelectE', \Mallto\Admin\Form\Field\MultipleSelect::class);
            //表单select控件:支持动态新增选项
            \Encore\Admin\Form::extend('selectOrNew', \Mallto\Admin\Form\Field\SelectOrNew::class);
            //表单富文本编辑器控件
            \Encore\Admin\Form::extend('editor2', \Mallto\Admin\Form\Field\WangEditor::class);
            //choice
            \Encore\Admin\Form::extend('choice', \Mallto\Admin\Form\Field\Choice::class);
            //embeds2,在原库空间的基础上,view页面使用了addElementClass设置了class
            \Encore\Admin\Form::extend('embeds2', \Mallto\Admin\Form\Field\Embeds::class);
            //qrcode
            \Encore\Admin\Form::extend('qrcode', \Mallto\Admin\Form\Field\QRcode::class);

            \Encore\Admin\Form::extend('hasMany2', \Mallto\Admin\Form\Field\HasMany::class);

            \Encore\Admin\Form::extend('displayE', \Mallto\Admin\Form\Field\Display::class);

            //表格扩展信息展示控件:支持点击按钮出现下拉展示信息表格
            \Encore\Admin\Grid\Column::extend("expand", \Mallto\Admin\Grid\Displayers\ExpandRow::class);
            //表格url控件:支持显示url二维码,和一键复制url
            \Encore\Admin\Grid\Column::extend("urlWrapper", \Mallto\Admin\Grid\Displayers\UrlWrapper::class);
            //表格数字格式化控件:支持格式化数字到指定位数
            \Encore\Admin\Grid\Column::extend("numberFormat",
                \Mallto\Admin\Grid\Displayers\NumberFomart::class);
            //表格switch控件:在laravel-admin switch的基础上,增加了对错误信息展示的处理
            \Encore\Admin\Grid\Column::extend("switchE", \Mallto\Admin\Grid\Displayers\SwitchDisplay::class);
            //表格switch控件:在laravel-admin switch的基础上,增加了对错误信息展示的处理，和修改弹窗确认
            \Encore\Admin\Grid\Column::extend("switchAlert",
                \Mallto\Admin\Grid\Displayers\SwitchAlertDisplay::class);
            //select:在laravel-admin select,增加了对错误信息展示的处理
            \Encore\Admin\Grid\Column::extend("selectE", \Mallto\Admin\Grid\Displayers\Select::class);
            //表格link控件:在laravel-admin的link的基础上,支持回调方法,可以获取当前操作的数据对象
            \Encore\Admin\Grid\Column::extend("linkE", \Mallto\Admin\Grid\Displayers\Link::class);

            Admin::js('vendor/laravel-adminE/clipboard/clipboard.min.js');
            Admin::js('vendor/laravel-adminE/admin_init.js');
            Admin::js('vendor/laravel-adminE/common.js');
            Admin::js('vendor/laravel-adminE/layer-v3.0.3/layer/layer.js');
            Admin::js('vendor/laravel-adminE/notify/notify.js');
            Admin::js('vendor/laravel-adminE/chartjs/Chart.min.js');
            Admin::js('vendor/laravel-adminE/jsQR/jsQR.js');

            //表格引入的两个库
//            Admin::js('https://cdn.bootcss.com/echarts/4.1.0.rc2/echarts.min.js');
//            Admin::js('https://cdnjs.cloudflare.com/ajax/libs/echarts/4.2.1/echarts.min.js');
            Admin::js('vendor/laravel-adminE/echarts/echarts.min.js');
            Admin::js('https://file.easy.mall-to.com/js/walden.js');
        });
    }

}
