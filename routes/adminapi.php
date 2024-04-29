<?php

/*
|--------------------------------------------------------------------------
| ADMIN API Routes
|--------------------------------------------------------------------------
|
| 这个文件中接口同时用于支持:
| 1. 第三方调用(走签名校验)
| 2. 管理端纯前端项目调用,走  api token 校验及接口权限校验
| 3. laravel管理端请求,走 web session 校验及接口权限校验
*/

use Illuminate\Support\Facades\Route;

/**
 * 本文件共有三类接口授权:
 * 1. 走web中间件的管理端请求(larave管理端) 接口前缀  domain/admin/web_api
 * 2. 走api和token授权校验的纯前端项目   接口前缀  domain/admin/api
 * 3. 走api/tp和接口权限校验(appid+secret)  domian/api/tp
 *
 * 如果要支持第三种方式使用的接口需要配置route name
 */

//$routeFunction接口支持通过1和2请求
$routeFunction = function () {
};

//$routeFunctionByAutoPermission支持1/2/3 三中方式请求
$routeFunctionByAutoPermission = function () {
//    Route::resource('tags', 'TagController');
    Route::get('select_data/{key}', '\Mallto\Admin\Controllers\SelectSourceController@dataSource');
};

//-------------- laravel 管理端项目请求 走web 中间件 start ---------------------------------------------------
$attributes = [
    'namespace' => 'Mallto\Tool\Controller',
    'middleware' => ['web'],
];

Route::group($attributes, function ($router) use ($routeFunction, $routeFunctionByAutoPermission) {

//----------------------------------------  管理端开始  -----------------------------------------------

    Route::group([
        'prefix' => 'admin/web_api',
        "middleware" => ['adminE'],
        'as' => 'web_api', // 配置路由组中路由命名的前缀。
    ], function ($router) use ($routeFunction, $routeFunctionByAutoPermission) {
        Route::group([
            'namespace' => 'Mallto\Tool\Controller\Admin\Api',
        ], $routeFunction);

        Route::group([
            'namespace' => 'Mallto\Tool\Controller\Admin\Api',
        ], $routeFunctionByAutoPermission);
    });
});
//-------------- laravel 管理端项目请求 走web 中间件 end ---------------------------------------------------

//-------------- 纯前端管理端项目请求用 start ---------------------------------------------------

Route::group([
    'prefix' => 'admin/api',
    'middleware' => ['owner_api', 'requestCheck', 'set_language'],
    'namespace' => 'Mallto\Tool\Controller\Admin\Api',
    'as' => 'admin_api', // 配置路由组中路由命名的前缀。
], function ($router) use ($routeFunction, $routeFunctionByAutoPermission) {
    Route::group([
        'middleware' => ['auth:admin_api'],
    ], $routeFunction);

    //-------- 接口权限校验 -----------
    Route::group([
        'middleware' => ['auth:admin_api'],
    ], function ($router) use ($routeFunctionByAutoPermission) {
        Route::group([
            'middleware' => ['adminE.auto_permission'],
        ], $routeFunctionByAutoPermission);
    });
});

//-------------- 纯前端管理端项目请求用 end ---------------------------------------------------

//----------------下面是第三方开发者可以调用，需要接口签名校验及接口权限校验的 start --------------
$attributes = [
    'namespace' => 'Mallto\Tool\Controller\Admin\Api',
    'prefix' => 'api/tp',
    'middleware' => ['api'],
    'as' => 'tp_api', // 配置路由组中路由命名的前缀。
];

Route::group($attributes, function ($router) use ($routeFunctionByAutoPermission) {
    Route::group(['middleware' => ['requestCheck', 'owner_api', 'authSign_referrer']],
        $routeFunctionByAutoPermission);
});


$attributes = [
    'namespace' => 'Mallto\Tool\Controller\Tp',
    'prefix' => 'api/tp',
    'middleware' => ['api'],
    'as' => 'tp_api', // 配置路由组中路由命名的前缀。
];

Route::group($attributes, function ($router) use ($routeFunctionByAutoPermission) {
    Route::group(['middleware' => ['requestCheck', 'owner_api', 'authSign_referrer']], function () {

    });
});

$attributes = [
    'namespace' => 'Mallto\Tool\Controller\Admin\Api',
    'prefix' => 'api',
    'middleware' => ['api'],
];

//----------------下面是第三方开发者可以调用，需要接口签名校验及接口权限校验的 end --------------
