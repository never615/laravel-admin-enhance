<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;

//----------------------------------------  管理端接口开始  -----------------------------------------------

//token 授权的管理端接口
Route::group([
    'prefix' => 'admin/api',
    'middleware' => ['oauth.providers', 'api', 'adminE.log','set_language'],
    'namespace' => 'Mallto\Admin\Controllers\Admin\Api',
], function ($router) {

    $router->post('auth/login', 'AuthController@postLogin');
    $router->get('auth/yzm', 'AuthController@captcha');

    Route::group([
        'middleware' => ['requestCheck'],
    ], function () {
        Route::group([
            'middleware' => ['multiauth:admin_api'],
        ], function ($router) {
            Route::group([
                'middleware' => ['adminE.auto_permission'],
            ], function ($router) {

            });

            $router->get('admin_user', 'AdminUserController@index');
        });
    });

});

//----------------------------------------  管理端接口结束  -----------------------------------------------

Route::get('/', 'Mallto\Admin\Controllers\WelcomeController@index');

Admin::routes();

Route::group([
    'namespace' => 'Mallto\Admin\Controllers',
    'middleware' => ['web'],
    'prefix' => config('admin.route.prefix'),
], function ($router) {

    //todo 这个权限暂时放在这
    Route::get('admin_bind_wechat', 'AdminBindWechatController@bindWechat');
    Route::get('admin_unbind_wechat', 'AdminBindWechatController@unbindWechat');
    $router->post('auth/send_sms', 'AuthController@sendSms');

    $router->get('auth/register', 'Admin\RegisterController@getRegister');
    $router->post('auth/register', 'Admin\RegisterController@postRegister');

//----------------------------------------  管理端开始  -----------------------------------------------
    Route::group(['middleware' => 'adminE_base'], function ($router) {
        $router->get('/', 'HomeController@index')->name('dashboard');

        //获取七牛upload token
        $router->get('uptoken', 'FileController@getUploadToken');
        //上传图片(富文本编辑器需要使用)
        $router->post('upload', 'FileController@upload');

        //$router->get('auth/login', '\Encore\Admin\Controllers\AuthController@getLogin');
//            $router->post('auth/login', '\Encore\Admin\Controllers\AuthController@postLogin');
        $router->post('auth/login', 'AuthController@postLogin');

        $router->get('auth/logout', '\Encore\Admin\Controllers\AuthController@getLogout');
        $router->get('auth/setting', 'AuthSettingController@getSetting');
        $router->put('auth/setting', 'AuthSettingController@putSetting');

        Route::get('select_data/{key}', 'SelectSourceController@dataSource');

        Route::group(['middleware' => ['adminE.auto_permission']], function ($router) {
            $router->resource('auth/admins', '\Mallto\Admin\Controllers\UserController');
            $router->resource('auth/roles', '\Mallto\Admin\Controllers\RoleController');
            $router->resource('auth/permissions', '\Mallto\Admin\Controllers\PermissionController');
            $router->resource('auth/menus', '\Mallto\Admin\Controllers\MenuController', ['except' => ['create']]);
            //$router->resource('auth/logs', '\Encore\Admin\Controllers\LogController',
            //    [ 'only' => [ 'index', 'destroy' ] ]);

            $router->resource('subjects', 'SubjectController');

            //ApiPermissionController
            $router->resource('auth/api_permissions', '\Mallto\Admin\Controllers\ApiPermissionController');
            //FrontMenuController
            $router->resource('auth/front_menus', '\Mallto\Admin\Controllers\FrontMenuController', ['except' => ['create']]);


            $router->resource('reports', 'ReportController');
            $router->resource('uploads', 'UploadController');
            $router->resource('videos', 'VideoController');

            //主体配置管理: 动态配置
            $router->resource('subject_configs', 'SubjectConfigController');

            //主体配置管理:一个表中的一行数据配置一个主体
            Route::resource('subject_settings', 'SubjectSettingController');

            //文件导入模块
            $router->resource('import_settings', 'Import\ImportSettingController');
            //导入记录
            $router->resource('import_records', 'Import\ImportRecordController');

            //账户分组
            Route::resource('admin_user_groups', 'AdminUserGroupController');

            Route::group([
                'namespace' => 'Admin',
            ], function ($router) {

                //操作日志字典
                Route::resource("operation_log_dictionarys", 'OperationLogDictionaryController');
                Route::resource("auth/logs", 'OperationLogController');

                //登录用户管理
                Route::resource("login_users", 'LoginUserController');
            });
        });
    });

//----------------------------------------  管理端结束  -----------------------------------------------

});





