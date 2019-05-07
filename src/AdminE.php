<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Route;
use Mallto\Admin\Data\Menu;


/**
 * Class Admin.
 */
class AdminE
{


    public function quickAccess()
    {
        $adminUser = Admin::user();

        if ($adminUser) {
            $speedy = \Illuminate\Support\Facades\Cache::get("speedy_".$adminUser->id);
            if (!$speedy) {
                $speedy = [];

                //读取对应主体中的快捷访问菜单配置
                $menuIds = SubjectUtils::getConfigByOwner(SubjectConfigConstants::OWNER_CONFIG_QUICK_ACCESS_MENU);

                if (!$menuIds) {
                    return;
                }
                $menus = Menu::find($menuIds);

                foreach ($menus as $menu) {
                    if ($adminUser->can($menu->uri)) {
                        $speedy = array_add($speedy, route($menu->uri, [], false), $menu->title);
                    }
                }

                \Illuminate\Support\Facades\Cache::put("speedy_".$adminUser->id, $speedy, 30);
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
    public
    function registerAuthRoutes()
    {

        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => ['adminE_base'],
        ];


        Route::group($attributes, function ($router) {
            $attributes = ['middleware' => ['adminE.auto_permission']];
            /* @var \Illuminate\Routing\Router $router */
            $router->group([$attributes], function ($router) {

                /* @var \Illuminate\Routing\Router $router */
                $router->resource('auth/admins', '\Mallto\Admin\Controllers\UserController');
                $router->resource('auth/roles', '\Mallto\Admin\Controllers\RoleController');
                $router->resource('auth/permissions', '\Mallto\Admin\Controllers\PermissionController');
                $router->resource('auth/menus', '\Mallto\Admin\Controllers\MenuController',
                    ['except' => ['create']]);
                $router->resource('auth/logs', '\Encore\Admin\Controllers\LogController',
                    ['only' => ['index', 'destroy']]);
            });

            $router->get('auth/login', '\Encore\Admin\Controllers\AuthController@getLogin');
            $router->post('auth/login', '\Encore\Admin\Controllers\AuthController@postLogin');
            $router->get('auth/logout', '\Encore\Admin\Controllers\AuthController@getLogout');
            $router->get('auth/setting', '\Encore\Admin\Controllers\AuthController@getSetting');
            $router->put('auth/setting', '\Encore\Admin\Controllers\AuthController@putSetting');
        });
    }

}
