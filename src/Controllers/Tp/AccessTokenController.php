<?php
/*
 * Copyright (c) 2024. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Tp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\SubjectUtils;

/**
 * User: never615 <never615.com>
 * Date: 2024/8/8
 * Time: 19:06
 */
class AccessTokenController extends Controller
{

    public function getByAdminUsername(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255|min:1|exists:admin_users,username',
        ]);
        $username = $request->username;

        //Administrator::query()->where('username', $username)->firstOrFail();
        $subjectId = SubjectUtils::getSubjectId();

        $adminUser = config('admin.auth.providers.admin.model')::query()->where('subject_id', $subjectId)
            ->where("username", $request->username)->firstOrFail();

        $token = $adminUser->createToken("admin_api");

//        return $token->plainTextToken;
        return $token;


    }
}