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

Route::group($attributes, function ($router) {

    //todo 这个权限暂时放在这
    Route::get('admin/admin_bind_wechat', 'AdminBindWechatController@bindWechat');


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

                //文件导入模块
                $router->resource("import_settings", 'Import\ImportSettingController');
                //导入记录
                $router->resource("import_records", 'Import\ImportRecordController');


            });
        });

//----------------------------------------  管理端结束  -----------------------------------------------


});





