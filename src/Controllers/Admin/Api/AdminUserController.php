<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\Domain\User\AdminUserUsecase;
use Mallto\Tool\Exception\PermissionDeniedException;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/28
 * Time: 5:51 PM
 */
class AdminUserController extends Controller
{

    public function index()
    {
        $adminUser = Auth::guard("admin_api")->user();

        //检查账号是否被禁用
        if ($adminUser->status == "forbidden") {
            throw new PermissionDeniedException("当前账号已被禁用");
        }

        $adminUserUsecase = app(AdminUserUsecase::class);

        return $adminUserUsecase->getReturnUserInfo($adminUser, true);
    }

}
