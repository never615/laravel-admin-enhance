<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

$attributes = [
    'namespace'  => 'Mallto\Admin\Controllers\Api',
    'prefix'     => 'api',
    'middleware' => [ 'api' ],
];

Route::group($attributes, function ($router) {

    /**
     * 需要经过验证
     */
    Route::group([ 'middleware' => [] ], function ($router) {

        //获取七牛upload token
        $router->get('uptoken', '\Mallto\Admin\Controllers\FileController@getUploadToken');

        //这个路由不能用resource,因为还有个路由是subject/config会冲突
        Route::get('subject', 'SubjectController@index');
        //主体动态配置
        Route::get('subject/config', 'SubjectConfigController@index');

        //项目配置
        Route::get('subject/setting', 'SubjectSettingController@index');

        //前端初始化配置
        Route::get('front_init_config', 'SubjectFrontConfigController@config');

        //NearestSubjectController
        Route::get('nearest_subject', 'NearestSubjectController@index');

        /**
         * 需要经过签名校验
         */
        Route::group([ 'middleware' => [ 'authSign_referrer' ] ], function () {

        });

        /**
         * 需要经过授权
         */
        Route::group([ 'middleware' => [ 'auth:api' ] ], function ($router) {


        });
    });
});





