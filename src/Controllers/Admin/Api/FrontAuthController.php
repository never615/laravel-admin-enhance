<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Mallto\Admin\Data\FrontAdminUser;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

class FrontAuthController extends Controller
{
    use ValidatesRequests, ThrottlesLogins;

    protected $maxAttempts = 5;

    protected $decayMinutes = 5;

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        if ($this->hasTooManyLoginAttempts($request)) {
            throw new ResourceException(Lang::get('auth.throttle', ['seconds' => $seconds]));
        }

        $user = FrontAdminUser::where('username', $request->username)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            $this->incrementLoginAttempts($request);
            throw new ResourceException(trans('auth.failed'));
        }

        $this->clearLoginAttempts($request);

        if ($user->status === 'forbidden') {
            throw new PermissionDeniedException('Account disabled');
        }

        $token = $user->createToken('front_admin')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('roles'),
        ]);
    }

    protected function username()
    {
        return 'username';
    }
}

