<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\Data\Traits\AdminUserTrait;
use Mallto\Admin\Data\Traits\DynamicData;
use Mallto\Admin\Data\Traits\SelectSource;

class Video extends Model
{

    use DynamicData, AdminUserTrait, SelectSource;

    protected $guarded = [

    ];


    public function getUrlAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix") . $value;
    }

}
