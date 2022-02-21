<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\User;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/28
 * Time: 6:28 PM
 */
interface AdminUserUsecase
{

    /**
     * 返回给前端的用户信息
     *
     * @param          $adminUser
     * @param bool     $addToken
     * @param string[] $permission
     *
     * @return mixed
     */
    public function getReturnUserInfo($adminUser, $addToken = true, $permission = [ 'admin_api_manager' ]);


    /**
     * 根据openid查询管理端用户
     *
     * @param $openid
     * @param $subjectId
     *
     * @return mixed
     */
    public function getUserByOpenid($openid, $subjectId);


    /**
     * 根据用户名密码查询用户
     *
     * @param $username
     * @param $password
     * @param $subjectId
     *
     * @return mixed
     */
    public function getUserByUsernameAndPassword($username, $password);


    /**
     * 获取管理用户对应的openid
     *
     * @param $adminUser
     *
     * @return mixed
     */
    public function getOpenid($adminUser);

}
