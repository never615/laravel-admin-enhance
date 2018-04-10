<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;


use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Mallto\Tool\Domain\Log\Logger;

/**
 * 记录管理端操作日志
 *
 * Class OperationLog
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
        if (config('admin.operation_log.enable') && Admin::user()) {

            $ip = "";
            $tempIp = $request->header("X-Forwarded-For");
            if ($tempIp) {
                $ip = $tempIp;
            } else {
                $ip = $request->getClientIp();
            }

            $log = [
                'user_id'    => Admin::user()->id,
                'path'       => $request->path(),
                'method'     => $request->method(),
                'request_ip' => $ip,
                'input'      => json_encode($request->input()),
                'subject_id' => Admin::user()->subject->id,
            ];

            $logger = resolve(Logger::class);
            $logger->logAdminOperation($log);
        }

        return $next($request);
    }
}
