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
use Mallto\Admin\Data\Permission;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\HttpException;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;
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
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $adminUser = Auth::guard("admin")->user();
        if ( ! $adminUser && ! empty(config('auth.guards.admin_api'))) {
            $adminUser = Auth::guard("admin_api")->user();
        }

        if ( ! $adminUser) {
            throw new PermissionDeniedException('未登录');
        }

        $subjectId = null;
        try {
            $subjectId = SubjectUtils::getSubjectId();
        } catch (HttpException $httpException) {

        }

        if ($subjectId) {
            if ( ! $adminUser->isOwner() && $adminUser->subject_id != $subjectId) {
                throw new ResourceException("登录账号没有权限请求该项目，adminUser subject与UUID不符");
            }
        }

        $currentRouteName = $request->route()->getName();
        $routenameArr = explode(".", $currentRouteName);

        if (count($routenameArr) == 2) {
            $subRouteName = $routenameArr[1];

            if ( ! Permission::where("slug", $currentRouteName)
                ->exists()) {
                if ($subRouteName === "edit" || $subRouteName === "show") {
                    $currentRouteName = $routenameArr[0] . ".index";
                }

                if ($subRouteName === "store" || $subRouteName === "update") {
                    $currentRouteName = $routenameArr[0] . ".create";
                }
            }
        }

        $this->exportPermissionHandler($request, $adminUser, $currentRouteName);

        if (is_null($currentRouteName)) {
            //todo 没有设置route name,使用uri来判断
            return $next($request);
        }

        if ($currentRouteName === 'admin.handle-action' || $currentRouteName === 'admin.handle-form') {
            return $next($request);
        }

        //权限管理有该权限,检查用户是否有该权限
        if ($adminUser->can($currentRouteName)) {
            //pass
            return $next($request);
        } else {
            //不拥有或者不存在对应权限的路由不能访问,控制面板除外
            //denied
            throw new AccessDeniedHttpException(trans("errors.permission_denied"));
        }
    }


    /**
     * 导出权限校验
     *
     * @param Request $request
     * @param         $adminUser
     * @param         $currentRouteName
     */
    private function exportPermissionHandler(Request $request, $adminUser, $currentRouteName)
    {
        //导出权限处理
        //导出权限的路由是xxx.index,即:/admin/customer_service_desks?_pjax=%23pjax-container&_export_=page%3A1
        //如果url参数包含_export_,则检查导出权限

        $export = $request->get("_export_");
        if ($export) {
            $currentRouteName = str_replace("index", "export", $currentRouteName);

            if ( ! $adminUser->can($currentRouteName)) {
                throw new AccessDeniedHttpException(trans("errors.permission_denied"));
            }
        }
    }
}
