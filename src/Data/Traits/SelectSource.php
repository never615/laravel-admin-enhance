<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;
use Mallto\Tool\Utils\AppUtils;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/04/2017
 * Time: 5:20 PM
 */
trait SelectSource
{

    public $selectName = "name";

    public $selectId = "id";


    /**
     * @return mixed
     * @deprecated
     */
    public static function selectSourceDate()
    {
        $isOwner = AdminUtils::isOwner();

        if ($isOwner) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"))
                ->pluck("name", "id")
                ->toArray();
        } else {
            return static::dynamicData()
                ->pluck("name", "id")
                ->toArray();
        }
    }


    public function scopeSelectByOwner($query)
    {
        return $query->select(\DB::raw("$this->selectName
        ||'-(主体id:'||subject_id||')' as $this->selectName,$this->selectId"));
    }


    public function scopeSelectBySubject($query)
    {
        return $query->select(\DB::raw("$this->selectName as $this->selectName,$this->selectId"));
    }


    public function scopeSelectSourceDatas($query)
    {
        return $query->selectSourceDatas2()
            ->pluck($this->selectName, $this->selectId)
            ->toArray();
    }


    /**
     * 与scopeSelectSourceDatas()相比,返回的是一个查询对象,不是查询结果
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeSelectSourceDatas2($query)
    {
        $isOwner = AdminUtils::isOwner();

        if ($isOwner && Schema::hasColumn($this->getTable(), 'subject_id')) {
            return $query->dynamicData()
                ->selectByOwner();
        } else {
            return $query->dynamicData()
                ->selectBySubject();
        }
    }


    /**
     * 排除则
     *
     * @param array $arr
     */
    public static function scopeArrayNoTest($arr = [], $except = [ 'test', 'other' ])
    {
        $except = array_merge([ 'test', 'other' ], $except);

        if (AppUtils::isProduction()) {
            return array_except($arr, $except);
        }

        return $arr;
    }
}
