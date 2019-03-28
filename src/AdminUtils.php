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
        $adminUser = session(CacheConstants::SESSION_ADMIN_USER);
        if (!$adminUser) {
            $adminUser = Admin::user();
        }
        $isOwner = session(CacheConstants::SESSION_IS_OWNER);

        if ($isOwner === null) {
            $isOwner = $adminUser->isOwner();
        }

        $currentSubject = session(CacheConstants::SESSION_CURRENT_SUBJECT);
        if (!$currentSubject) {
            $currentSubject = $adminUser->subject;
        }

        return [$adminUser, $isOwner, $currentSubject];
    }


    public static function getSubject($id)
    {
        $subject = Cache::get("subject_".$id);

        if (!$subject) {
            $subject = Subject::find($id);
            self::cacheSubject($subject);
        }

        return $subject;
    }


    public static function cacheSubject($subject)
    {
        Cache::forever("subject_".$subject->id, $subject);
    }

    public static function forgetSubject($id)
    {
        Cache::forget("subject_".$id);
    }
}