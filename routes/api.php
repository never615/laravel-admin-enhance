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
    'middleware' => ['api'],
];

Route::group($attributes, function ($router) {

    /**
     * 需要经过验证
     */
    Route::group(['middleware' => ['requestCheck','owner_api']], function ($router) {

        //获取七牛upload token
        $router->get('uptoken', '\Mallto\Admin\Controllers\FileController@getUploadToken');

        /**
         * 需要经过签名校验
         */
        Route::group(['middleware' => ['authSign']], function () {

        });


        /**
         * 需要经过授权
         */
        Route::group(['middleware' => ['auth:api']], function ($router) {



        });
    });
});





