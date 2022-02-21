<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\User;

use Illuminate\Support\Facades\Hash;
use Mallto\Admin\Data\Traits\PermissionHelp;
use Mallto\Admin\Domain\Permission\PermissionUsecase;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Data\User;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/28
 * Time: 6:26 PM
 */
class AdminUserUsecaseImpl implements AdminUserUsecase
{

    use PermissionHelp;

    /**
     * @var PermissionUsecase
     */
    private $permissionUsecase;


    /**
     * AdminUserUsecaseImpl constructor.
     *
     * @param PermissionUsecase $permissionUsecase
     */
    public function __construct(PermissionUsecase $permissionUsecase)
    {
        $this->permissionUsecase = $permissionUsecase;
    }


    /**
     * 返回给前端的用户信息
     *
     * @param          $adminUser
     * @param bool     $addToken
     * @param string[] $permission
     *
     * @return mixed
     */
    public function getReturnUserInfo($adminUser, $addToken = true, $permission = [ 'admin_api_manager' ])
    {
        $adminable = $adminUser->adminable;
        if ($addToken) {
            $token = $adminUser->createToken('admin_api');
            $adminUser->token = $token->accessToken;
        }

        $permissions = [];

        if ( ! empty($permission)) {
            foreach ($permission as $item) {
                $tempPermissions = $this->permissionUsecase->getUserPermissionForModule($adminUser,
                    $item);
                if ($tempPermissions) {
                    $permissions = array_merge($permissions, $tempPermissions);
                }
            }
        }

        return array_merge($adminUser->only([
            'id',
            'name',
            'username',
            'adminable_type',
            'adminable_id',
        ]), [
            'adminable'   => $adminable->only([
                'name',
            ]),
            'uuid'        => $adminable->uuid,
            "token"       => $token->accessToken,
            "permissions" => $permissions,
        ]);
    }


    /**
     * 根据openid查询管理端用户
     *
     * @param $openid
     * @param $subjectId
     *
     * @return mixed
     */
    public function getUserByOpenid($openid, $subjectId)
    {
        $class = config('auth.providers.admin_users.model');

        $adminUser = $class::with([ "adminable" ])
            ->where("subject_id", $subjectId)
            ->where("openid->" . $openid . "->openid", $openid)
            ->first();
        if ( ! $adminUser) {
            //管理端账户不存在

            //查询该openid对应会员的手机号,是否已经绑定了管理端账户
            //这个是兼容旧的管理端账户绑定,直接输入会员的手机号

            $user = User::with([
                'userAuths' => function ($query) use ($openid) {
                    $query->where('identity_type', 'wechat')
                        ->where('identifier', $openid);
                },
            ])->where('subject_id', $subjectId)->first();

            if ($user && $user->mobile) {
                $adminUser = $class::with([ 'adminable' ])
                    ->where('subject_id', $subjectId)
                    ->where('mobile', $user->mobile)
                    ->first();
            }
        }

        return $adminUser;
    }


    /**
     * 获取管理用户对应的openid
     *
     * @param $adminUser
     *
     * @return mixed
     */
    public function getOpenid($adminUser)
    {
        if ( ! empty($adminUser->openid)) {
            //todo 目前暂时返回一个绑定的openid，需要优化支持推送多个微信绑定用户模板消息
            $openid = null;
            foreach ($adminUser->openid as $key => $value) {
                $openid = $key;
            }

            return $openid;
        } else {
            $user = User::where('mobile', $adminUser->mobile)
                ->where('subject_id', $adminUser->subject_id)
                ->first();
            if ($user) {
                $userAuth = $user->userAuths()->where('identity_type', 'wechat')
                    ->first();
                if ($userAuth) {
                    return $userAuth->identifier;
                }
            }

            return null;
        }

    }


    /**
     * 根据用户名密码查询用户
     *
     * @param $username
     * @param $password
     *
     * @return mixed
     */
    public function getUserByUsernameAndPassword($username, $password)
    {
        $class = config('auth.providers.admin_users.model');

        $adminUser = $class::where([
            'username' => $username,
        ])->first();

        if ( ! $adminUser) {
            throw new ResourceException('登录账号不存在');
        }

        if (Hash::check($password, $adminUser->password)) {
            // 密码匹配...
            return $adminUser;
        }

        return null;
    }
}
