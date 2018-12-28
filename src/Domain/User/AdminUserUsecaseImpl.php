<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\User;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/28
 * Time: 6:26 PM
 */
class AdminUserUsecaseImpl implements AdminUserUsecase
{

    /**
     * 返回给前端的用户信息
     *
     * @param      $adminUser
     * @param bool $addToken
     * @return mixed
     */
    public function getReturnUserInfo($adminUser, $addToken = true)
    {
        $adminable = $adminUser->adminable;
        if ($addToken) {
            $token = $adminUser->createToken("admin_api");
            $adminUser->token = $token->accessToken;
        }


        return array_merge($adminUser->only([
            "id",
            "name",
            "username",
            "adminable_type",
            "adminable_id",
            "token",
        ]), [
            "adminable" => $adminable->only([
                "name",
            ]),
        ]);
    }
}