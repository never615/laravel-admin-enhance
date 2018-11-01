<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


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
        if (Admin::user()->isOwner()) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"))->pluck("name", "id");
        } else {
            return static::dynamicData()->pluck("name", "id");
        }
    }


    public function scopeSelectSourceDatas()
    {
        if (Admin::user()->isOwner() && Schema::hasColumn($this->getTable(), 'subject_id')) {
            return static::dynamicData()
                ->select(DB::raw("name||'-'||subject_id as name,id"))->pluck("name", "id");
        } else {
            return static::dynamicData()->pluck("name", "id");
        }
    }

    public function scopeSelectSourceDatas2()
    {
        if (Admin::user()->isOwner()) {
            if (Schema::hasColumn($this->getTable(), 'subject_id')) {
                return static::dynamicData()
                    ->select(DB::raw("name||'-'||subject_id as name,id"));
            } else {
                return static::dynamicData()
                    ->select(DB::raw("name as name,id"));
            }
        } else {
            return static::dynamicData();
        }
    }


}
