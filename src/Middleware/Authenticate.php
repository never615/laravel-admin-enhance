<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
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
        \config([ 'auth.defaults.guard' => 'admin' ]);

        if (Admin::guard()->guest() && ! $this->shouldPassThrough($request)) {
            if ($request->expectsJson()) {
                return response()->json([ 'error' => "未授权,请登录" ], 401);
            } else {
                $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));

                if ($this->shouldRememberIntendedUrl($request)) {
                    return redirect()->guest($redirectTo);
                }

                return redirect($redirectTo);
            }
        }

        $adminUser = Admin::user();

        //检查账号是否被禁用
        if ($adminUser && $adminUser->status === "forbidden") {
            Admin::guard()->logout();

            $request->session()->invalidate();

            throw new PermissionDeniedException('当前账号:'.$adminUser->name.'已被禁用');
        }

        return $next($request);
    }


    /**
     * Only user-initiated top-level page navigations may decide where login should return.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldRememberIntendedUrl($request)
    {
        if (! $request->isMethod('GET')
            || $request->ajax()
            || $request->pjax()
            || $request->get('mt_pjax')) {
            return false;
        }

        // Modern browsers only send Sec-Fetch-User for user-activated navigation.
        // This prevents automatic page reloads from changing the post-login destination.
        if ($request->headers->has('Sec-Fetch-User')) {
            return $request->header('Sec-Fetch-User') === '?1';
        }

        if ($request->headers->has('Sec-Fetch-Mode') || $request->headers->has('Sec-Fetch-Dest')) {
            return false;
        }

        return true;
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
