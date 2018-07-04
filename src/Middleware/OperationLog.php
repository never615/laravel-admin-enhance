<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;


use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mallto\Tool\Domain\Log\Logger;

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
        if (!$adminUser) {
            $adminUser = Auth::guard("admin_api")->user();
        }

        if (config('admin.operation_log.enable') && $adminUser) {

            $ip = "";
            $tempIp = $request->header("X-Forwarded-For");
            if ($tempIp) {
                $ip = $tempIp;
            } else {
                $ip = $request->getClientIp();
            }

            $log = [
                'user_id'    => $adminUser->id,
                'path'       => $request->path(),
                'method'     => $request->method(),
                'request_ip' => $ip,
                'input'      => json_encode($request->input()),
                'subject_id' => $adminUser->subject->id,
            ];

            $logger = resolve(Logger::class);
            $logger->logAdminOperation($log);
        }

        return $next($request);
    }
}
