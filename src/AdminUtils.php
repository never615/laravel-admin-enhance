<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/3/28
 * Time: 7:17 PM
 */
class AdminUtils
{
    /**
     * 获取当前登录用户的信息
     *
     * @return array
     */
    public static function getLoginUserData()
    {
//        $adminUser = session(CacheConstants::SESSION_ADMIN_USER);
        $isOwner = session(CacheConstants::SESSION_IS_OWNER);
        $currentSubject = session(CacheConstants::SESSION_CURRENT_SUBJECT);

        if ($isOwner === null || !$currentSubject) {
            $adminUser = Admin::user();
            $isOwner = $adminUser->isOwner();
            $currentSubject = $adminUser->subject;

            session([
//                CacheConstants::SESSION_ADMIN_USER      => $adminUser,
                CacheConstants::SESSION_IS_OWNER        => ($adminUser->isOwner() ? 1 : 0),
                CacheConstants::SESSION_CURRENT_SUBJECT => $adminUser->subject->toArray(),
            ]);
        }


        return [null, $isOwner, (object) $currentSubject];
    }


    /**
     * 判断当前登录用户是否是owner
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public static function isOwner()
    {
        $isOwner = session(CacheConstants::SESSION_IS_OWNER);

        if ($isOwner === null) {
            [$adminUser, $isOwner, $currentSubject] = self::getLoginUserData();
        }

        return $isOwner;
    }


    /**
     * 根据主体id 查询 subject
     *
     * @param $id
     * @return mixed
     */
    public static function getSubject($id)
    {
        $subject = Cache::get("subject_".$id);

        if (!$subject) {
            $subject = Subject::find($id);
            self::cacheSubject($subject);
        }

        return $subject;
    }


    /**
     * 缓存主体数据
     *
     * @param $subject
     */
    public static function cacheSubject($subject)
    {
        Cache::forever("subject_".$subject->id, $subject);
    }

    /**
     * 删除缓存的主体数据
     *
     * @param $id
     */
    public static function forgetSubject($id)
    {
        Cache::forget("subject_".$id);
    }
}