<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use Mallto\Tool\Exception\PermissionDeniedException;

class Authenticate
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->guest() && ! $this->shouldPassThrough($request)) {
            if ($request->expectsJson()) {
                return response()
                    ->json([ 'error' => "未授权,请登录" ], 401);
            } else {
                return redirect()->guest(admin_base_path('auth/login'));
            }
        }

        $adminUser = Admin::user();

        //检查账号是否被禁用
        if ($adminUser && $adminUser->status == "forbidden") {
            Admin::guard()->logout();

            $request->session()->invalidate();

            throw new PermissionDeniedException('当前账号:' . $adminUser->name . '已被禁用');
        }

        return $next($request);
    }


    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = [
            admin_base_path('auth/login'),
            admin_base_path('auth/logout'),
        ];

        foreach ($excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
