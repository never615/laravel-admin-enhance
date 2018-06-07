<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;


/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/04/2017
 * Time: 5:20 PM
 */
trait SelectSource
{
    public static function selectSourceDate()
    {
        if (Admin::user()->isOwner()) {
            return static::dynamicData()
                ->select(DB::raw("name||subject_id as name,id"))->pluck("name", "id");
        } else {
            return static::dynamicData()->pluck("name", "id");
        }
    }

}
