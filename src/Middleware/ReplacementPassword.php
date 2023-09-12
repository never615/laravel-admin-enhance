<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Carbon\Carbon;
use Closure;
use Encore\Admin\Facades\Admin;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

class ReplacementPassword
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $configApp = config('app.unique');
        //控制密码重置功能
        if ($configApp === 'bank') {
            $adminUser = Admin::user();
            if ($adminUser) {
                if ($adminUser->replacement_password_time === null) {
                    admin_error('error', '重置密码后,方可登录系统。');
                    return redirect('/admin/auth/setting');
                }

                $replacementPasswordTime = Carbon::parse($adminUser->replacement_password_time);

                // Calculate the current time
                $currentTime = Carbon::now();

                // Calculate the next password change time (90 days)
                $nextPasswordChangeTime = $replacementPasswordTime->copy()->addDays(90);

                // Calculate the prompt time (75 days)
                $promptTime = $replacementPasswordTime->copy()->addDays(75);

                if ($currentTime >= $nextPasswordChangeTime) {
                    // Password has expired, force the user to change it
                    admin_error('error', '您的密码已过期, 请重置您的密码。');
                    return redirect('/admin/auth/setting');
                } elseif ($currentTime >= $promptTime) {
                    // Prompt the user to change their password
                    admin_error('warning', '您的密码即将过期, 出于安全考虑，请重置您的密码。');
                }
            }
        }
        return $next($request);
    }
}
