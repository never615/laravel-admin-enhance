<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin;


use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Mallto\Admin\CacheConstants;
use Mallto\Admin\CacheKeyConstants;

class AuthController extends \Encore\Admin\Controllers\AuthController
{

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }


    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        $adminUser = Admin::user();

        session([
            CacheConstants::SESSION_ADMIN_USER      => $adminUser,
            CacheConstants::SESSION_IS_OWNER        => ($adminUser->isOwner() ? 1 : 0),
            CacheConstants::SESSION_CURRENT_SUBJECT => $adminUser->subject,
        ]);

        return redirect()->intended($this->redirectPath());
    }


}
