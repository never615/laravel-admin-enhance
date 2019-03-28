<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;


/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/04/2017
 * Time: 5:20 PM
 */
trait SelectSource
{
    /**
     * @deprecated
     * @return mixed
     */
    public static function selectSourceDate()
    {
        [$adminUser, $isOwner, $currentSubject] = AdminUtils::getLoginUserData();

        if ($isOwner) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"))->pluck("name", "id");
        } else {
            return static::dynamicData()->pluck("name", "id");
        }
    }


    public function scopeSelectSourceDatas()
    {
        [$adminUser, $isOwner, $currentSubject] = AdminUtils::getLoginUserData();

        if ($isOwner && Schema::hasColumn($this->getTable(), 'subject_id')) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"))->pluck("name", "id");
        } else {
            return static::dynamicData()->pluck("name", "id");
        }
    }

    /**
     * 与scopeSelectSourceDatas()相比,返回的是一个查询对象,不是查询结果
     *
     * @return mixed
     */
    public function scopeSelectSourceDatas2()
    {
        [$adminUser, $isOwner, $currentSubject] = AdminUtils::getLoginUserData();

        if ($isOwner && Schema::hasColumn($this->getTable(), 'subject_id')) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"));
        } else {
            return static::dynamicData()
                ->select(DB::raw("name as name,id"));
        }
    }


}
