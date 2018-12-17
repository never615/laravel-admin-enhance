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


use Illuminate\Support\Facades\Route;

$attributes = [
    'namespace'  => 'Mallto\Admin\Controllers',
    'middleware' => ['web'],
];

//token 授权的管理端接口
Route::group([
    'prefix'     => "admin/api",
    "middleware" => ["oauth.providers", "api", "adminE.log"],
    'namespace'  => 'Mallto\Admin\Controllers',
], function ($router) {

    $router->post('auth/login', '\Mallto\Admin\Controllers\AuthController@postLogin');

    Route::group([
        "middleware" => ["auth:admin_api", "adminE.auto_permission"],
        "namespace"  => "Admin",
    ],
        function ($router) {

        });
});

\Mallto\Admin\Facades\AdminE::registerAuthRoutes();


Route::group($attributes, function ($router) {

    //todo 这个权限暂时放在这
    Route::get('admin/admin_bind_wechat', 'AdminBindWechatController@bindWechat');
    Route::get('admin/admin_unbind_wechat', 'AdminBindWechatController@unbindWechat');


//----------------------------------------  管理端开始  -----------------------------------------------
    Route::group(['prefix' => config('admin.route.prefix'), "middleware" => "adminE_base"],
        function ($router) {
            $router->get('/', 'HomeController@index')->name("dashboard");

            //获取七牛upload token
            $router->get('uptoken', 'FileController@getUploadToken');
            //上传图片(富文本编辑器需要使用)
            $router->post('upload', 'FileController@upload');


            Route::group(['middleware' => ['adminE.auto_permission']], function ($router) {
                $router->resource("subjects", "SubjectController");

                $router->resource("reports", "ReportController");
                $router->resource("uploads", "UploadController");
                $router->resource("videos", "VideoController");

                //主体配置管理
                $router->resource("subject_configs", 'SubjectConfigController');

                //文件导入模块
                $router->resource("import_settings", 'Import\ImportSettingController');
                //导入记录
                $router->resource("import_records", 'Import\ImportRecordController');

                //账户分组
                Route::resource('admin_user_groups', 'AdminUserGroupController');


            });
        });

//----------------------------------------  管理端结束  -----------------------------------------------


});





