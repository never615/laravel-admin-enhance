<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\User;

use Mallto\Admin\Data\Administrator;
use Mallto\User\Data\User;

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

    /**
     * 根据openid查询管理端用户
     *
     * @param $openid
     * @param $subjectId
     * @return mixed
     */
    public function getUserByOpenid($openid, $subjectId)
    {
        $class = config('auth.providers.admin_users.model');

        $adminUser = $class::with(["adminable"])
            ->where("subject_id", $subjectId)
            ->where("openid->openid", $openid)
            ->first();
        if (!$adminUser) {
            //管理端账户不存在

            //查询该openid对应会员的手机号,是否已经绑定了管理端账户
            //这个是兼容旧的管理端账户绑定,直接输入会员的手机号

            $user = User::with([
                'userAuths' => function ($query) use ($openid) {
                    $query->where("identity_type", "wechat")
                        ->where("identifier", $openid);
                },
            ])->where("subject_id", $subjectId)->first();


            if ($user && $user->mobile) {
                $adminUser = $class::with(["adminable"])
                    ->where("subject_id", $subjectId)
                    ->where("mobile", $user->mobile)
                    ->first();
            }
        }

        return $adminUser;
    }

    /**
     * 获取管理用户对应的openid
     *
     * @param $adminUser
     * @return mixed
     */
    public function getOpenid($adminUser)
    {
        if (!empty($adminUser->openid["openid"])) {
            return $adminUser->openid["openid"];
        } else {
            $user = User::where("mobile", $adminUser->mobile)
                ->where("subject_id", $adminUser->subject_id)
                ->first();
            if ($user) {
                $userAuth = $user->userAuths()->where("identity_type", "wechat")
                    ->first();
                if ($userAuth) {
                    return $userAuth->identifier;
                }
            }

            return null;
        }

    }
}