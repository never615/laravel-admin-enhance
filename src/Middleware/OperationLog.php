<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;


use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Jobs\LogJob;

/**
 * 记录管理端操作日志
 *
 * Class OperationLog
 *
 * @package Mallto\Tool\Middleware
 */
class OperationLog
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {

        $adminUser = Admin::user();
        if (!$adminUser && !empty(config('auth.guards.admin_api'))) {
            $adminUser = Auth::guard("admin_api")->user();
        }

        if (config('admin.operation_log.enable') && $adminUser) {

            $ip = 0;
            $tempIp = $request->header("X-Forwarded-For");
            if ($tempIp) {
                $ip = $tempIp;
            } else {
                $ip = $request->getClientIp();
            }

            $log = [
                'uuid'       => SubjectUtils::getUUIDNoException() ?: 0,
                'user_id'    => $adminUser->id,
                'path'       => $request->path(),
                'method'     => $request->method(),
                'request_ip' => $ip,
                'input'      => json_encode($request->input()),
                'subject_id' => $adminUser->subject->id,
                "action"     => "request",
            ];

            dispatch(new LogJob("logAdminOperation", $log));
        }

        $response = $next($request);
        if (is_array($response->getContent())) {
            $input = json_encode($response->getContent());
        } else {
            if (is_string($response->getContent())) {
                try {
                    $input = json_decode($response->getContent());
                    if (is_null($input)) {
                        $input = "非json数据";
                    } else {
                        $input = $response->getContent();
                    }
                } catch (\Exception $exception) {
                    $input = "异常数据";
                }
            } else {
                $input = "其他数据";
            }
        }

        $log = [
            'uuid'       => SubjectUtils::getUUIDNoException() ?: 0,
            'user_id'    => $adminUser->id,
            'path'       => $request->path(),
            'method'     => $request->method(),
            'request_ip' => $ip,
            'input'      => $input,
            'subject_id' => $adminUser->subject->id,
            'action'     => "response",
            'status'     => $response->getStatusCode(),
        ];


        dispatch(new LogJob("logAdminOperation", $log));


        return $response;
    }
}
