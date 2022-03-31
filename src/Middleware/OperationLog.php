<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Middleware\LogOperation;
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
class OperationLog extends LogOperation
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ( ! $this->shouldLogOperation($request)) {
            return $next($request);
        }

        $adminUser = null;

        try {
            $adminUser = Admin::user();
            if ( ! $adminUser && ! empty(config('auth.guards.admin_api'))) {
                $adminUser = Auth::guard("admin_api")->user();
            }
        } catch (\Exception $exception) {
            \Log::warning('OperationLog');
            \Log::warning($exception);
            try {
                $adminUser = Auth::guard("admin_api")->user();
            } catch (\Exception $exception) {
                \Log::warning('OperationLog2');
                \Log::warning($exception);
            }
        }
        if ( ! $adminUser) {
            return $next($request);
        }

        $ip = 0;
        $tempIp = $request->header("X-Forwarded-For");
        if ($tempIp) {
            $ip = $tempIp;
        } else {
            $ip = $request->getClientIp();
        }

        $log = [
            'uuid'       => SubjectUtils::getUUIDNoException() ?: 0,
            'user_id'    => $adminUser->id ?? 0,
            'path'       => $request->path(),
            'method'     => $request->method(),
            'request_ip' => $ip,
            'input'      => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
            'header'     => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE),
            'subject_id' => $adminUser->subject->id ?? 0,
            "action"     => "request",
        ];

        dispatch(new LogJob("logAdminOperation", $log));

        $response = $next($request);
        $content = $response->getContent();

        if (is_array($content)) {
            $input = json_encode($content);
        } else {
            if ( ! is_string($content)) {
                $input = "其他数据";
            } else {
                $input = $content;
            }
        }

        $log = [
            'uuid'       => SubjectUtils::getUUIDNoException() ?: 0,
            'user_id'    => $adminUser->id ?? 0,
            'path'       => $request->path(),
            'method'     => $request->method(),
            'request_ip' => $ip,
            'input'      => $input,
            'subject_id' => $adminUser->subject->id ?? 0,
            'action'     => "response",
            'status'     => $response->getStatusCode(),
        ];

        dispatch(new LogJob("logAdminOperation", $log));

        return $response;
    }

}
