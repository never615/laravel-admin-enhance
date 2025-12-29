<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\HttpException;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class FrontAutoPermissionMiddleware
 *
 * 纯前端管理端平台请求的 api 接口的权限校验中间件.
 * 用户必须是 front_admin_api guard 下的用户才可以通过此中间件.
 * 路由多是 admin/api/ 前缀下的路由.
 *
 * @package Mallto\Admin\Middleware
 */
class FrontAutoPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $adminUser = Auth::guard('front_admin_api')->user();

        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        $subjectId = null;
        try {
            $subjectId = SubjectUtils::getSubjectId();
        } catch (HttpException $httpException) {
            throw new ResourceException($httpException->getMessage());
        }

        if (!$subjectId) {
            throw new ResourceException('Cannot resolve subject_id for current request');
        }

        if ($adminUser->subject_id != $subjectId) {
            throw new ResourceException('Current account has no permission to access this project (subject mismatch)');
        }

        if (!$adminUser->isOwner() && $adminUser->subject_id != $subjectId) {
            throw new ResourceException('Current account has no permission to access this project (subject mismatch)');
        }

        $currentRouteName = $request->route()->getName();
        $routenameArr = explode(".", $currentRouteName);

        if (count($routenameArr) == 2) {
            $subRouteName = $routenameArr[1];

            if (!AdminApiPermission::where("slug", $currentRouteName)
                ->exists()) {
                if ($subRouteName === "edit" || $subRouteName === "show") {
                    $currentRouteName = $routenameArr[0] . ".index";
                }

                if ($subRouteName === "store" || $subRouteName === "update") {
                    $currentRouteName = $routenameArr[0] . ".create";
                }
            }
        }

        //todo 统一处理导出权限

        if (is_null($currentRouteName)) {
            //没有设置route name,使用uri来判断,目前直接通过,目前应该没有这种情况了
            return $next($request);
        }

        //权限管理有该权限,检查用户是否有该权限
        if ($adminUser->canApi($currentRouteName)) {
            //pass
            return $next($request);
        } //denied
        else {
            throw new AccessDeniedHttpException('admin api ' . trans("errors.permission_denied"));
        }

        return $next($request);
    }
}

