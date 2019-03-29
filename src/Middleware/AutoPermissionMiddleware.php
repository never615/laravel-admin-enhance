<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 10/03/2017
 * Time: 8:36 PM
 *
 * You need set permission's slug by outeName or url( auth/roles of https://xxx.com/admin/auth/roles )
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Permission;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AutoPermissionMiddleware
 *
 * 参考文档:https://github.com/never615/laravel-admin/wiki/%E9%A1%B9%E7%9B%AE%E8%AE%BE%E8%AE%A1#关于自动校验权限
 *
 * @package Encore\Admin\Middleware
 */
class AutoPermissionMiddleware
{
    protected $except = [
    ];

    /**
     * Handle an incoming request.
     *
     * @param         $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $adminUser = Auth::guard("admin")->user();
        if (!$adminUser && !empty(config('auth.guards.admin_api'))) {
            $adminUser = Auth::guard("admin_api")->user();
        }

        $currentRouteName = $request->route()->getName();
        $routenameArr = explode(".", $currentRouteName);

        if (count($routenameArr) == 2) {
            $subRouteName = $routenameArr[1];

            if (!Permission::where("slug", $currentRouteName)
                ->exists()) {
                if ($subRouteName == "edit" || $subRouteName == "show") {
                    $currentRouteName = $routenameArr[0].".index";
                }

                if ($subRouteName == "store" || $subRouteName == "update") {
                    $currentRouteName = $routenameArr[0].".create";
                }
            }
        }


        if (is_null($currentRouteName)) {
            //没有设置route name,使用uri来判断 todo
            return $next($request);
        }


        //权限管理有该权限,检查用户是否有该权限

        if ($adminUser->can($currentRouteName)) {
            //pass
            return $next($request);
        } else {
//            throw new AccessDeniedHttpException(trans("errors.permission_denied"));
            if ($adminUser->can($routenameArr[0])) {
                //拥有父权限,则通过所有子权限
                //pass 因为一个模块下面有增删改查子权限,懒得创建,就通过拥有父级的
                return $next($request);
            } else {
                //不拥有或者不存在对应权限的路由不能访问,控制面板除外
                //denied
                throw new AccessDeniedHttpException(trans("errors.permission_denied"));
            }
        }


    }
}
