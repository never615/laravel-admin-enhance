<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Mallto\Admin\Data\Menu;

/**
 * Class Admin.
 */
class AdminE
{

    /**
     * 主体配置类
     *
     * 每个库都可以扩展自己的主体配置
     */
    public $extendSubjectConfigClass = [];

    /**
     * 主体设置类:用来替代$extendSubjectConfigClass 的方案,
     * 因为$extendSubjectConfigClass的配置很多都在subjects表,
     * 导致subject越来越打
     *
     * 每个库都可以扩展自己的主体配置
     */
    public $extendSubjectSettingClass = [];

    /**
     * select source 类
     *
     * 每个库都可以扩展自己的select source
     *
     * @var array
     */
    public $extendSelectSourceClass = [];


    public function extendSubjectSettingClass($class)
    {
        $this->extendSubjectSettingClass[] = $class;
        $this->extendSubjectSettingClass = array_unique($this->extendSubjectSettingClass);
    }


    public function getSubjectSettingClass()
    {
        return $this->extendSubjectSettingClass;
    }


    public function extendSubjectConfigClass($class)
    {
        $this->extendSubjectConfigClass[] = $class;
        $this->extendSubjectConfigClass = array_unique($this->extendSubjectConfigClass);
    }


    public function getSubjectConfigClass()
    {
        return $this->extendSubjectConfigClass;
    }


    public function extendSelectSourceClass($class)
    {
        $this->extendSelectSourceClass[] = $class;
        $this->extendSelectSourceClass = array_unique($this->extendSelectSourceClass);
    }


    public function getSelectSourceClass()
    {
        return $this->extendSelectSourceClass;
    }


    /**
     * 顶部快捷访问初始化
     */
    public function quickAccess()
    {
        $adminUser = Admin::user();

        if ($adminUser) {
            //$menuIds = SubjectUtils::getConfigBySubjectOwner(SubjectConfigConstants::SUBJECT_OWNER_CONFIG_QUICK_ACCESS_MENU);
            //if ( ! $menuIds) {
            //    return;
            //}

            $speedy = Cache::get("speedy_" . $adminUser->id);
            if ( ! $speedy) {
                $speedy = [];

                //读取对应主体中的快捷访问菜单配置
                $menuIds = SubjectUtils::getConfigBySubjectOwner(SubjectConfigConstants::SUBJECT_OWNER_CONFIG_QUICK_ACCESS_MENU);

                if ( ! $menuIds) {
                    return;
                }

                $menus = Menu::find($menuIds);

                foreach ($menus as $menu) {
                    if ($adminUser->can($menu->uri)) {
                        $speedy = array_add($speedy, route($menu->uri, [], false), $menu->title);
                    }
                }

                CacheUtils::putSeedy($adminUser, $speedy);
            }

            if (count($speedy) > 0) {
                Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) use ($speedy) {
                    $navbar->left(view('adminE::partials.left_navbar')
                        ->with("speedy", $speedy));
                });
            }
        }
    }


    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public
    function menu()
    {
        return (new Menu())->toTree();
    }


    /**
     * Register the auth routes.
     *
     * @return void
     */
    //public
    //function registerAuthRoutes()
    //{
    //
    //    $attributes = [
    //        'prefix'     => config('admin.route.prefix'),
    //        'middleware' => [ 'adminE_base' ],
    //    ];
    //
    //    Route::group($attributes, function ($router) {
    //        $attributes = [ 'middleware' => [ 'adminE.auto_permission' ] ];
    //        /* @var \Illuminate\Routing\Router $router */
    //        $router->group([ $attributes ], function ($router) {
    //
    //            /* @var \Illuminate\Routing\Router $router */
    //            $router->resource('auth/admins', '\Mallto\Admin\Controllers\UserController');
    //            $router->resource('auth/roles', '\Mallto\Admin\Controllers\RoleController');
    //            $router->resource('auth/permissions', '\Mallto\Admin\Controllers\PermissionController');
    //            $router->resource('auth/menus', '\Mallto\Admin\Controllers\MenuController',
    //                [ 'except' => [ 'create' ] ]);
    //            $router->resource('auth/logs', '\Encore\Admin\Controllers\LogController',
    //                [ 'only' => [ 'index', 'destroy' ] ]);
    //        });
    //
    //        $router->get('auth/login', '\Encore\Admin\Controllers\AuthController@getLogin');
    //        $router->post('auth/login', '\Encore\Admin\Controllers\AuthController@postLogin');
    //        $router->get('auth/logout', '\Encore\Admin\Controllers\AuthController@getLogout');
    //        $router->get('auth/setting', '\Encore\Admin\Controllers\AuthController@getSetting');
    //        $router->put('auth/setting', '\Encore\Admin\Controllers\AuthController@putSetting');
    //    });
    //}


    public function adminBootstrap()
    {
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
    }

}
