<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mallto\Admin\Data\Traits\AdminUserTrait;
use Mallto\Admin\Data\Traits\DynamicData;

class Upload extends Model
{

    use DynamicData, AdminUserTrait;

    protected $guarded = [

    ];


    public function url(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (empty($value)) {
                    return null;
                }
                if (Str::startsWith($value, "http")) {
                    return $value;
                }

                return config("app.file_url_prefix") . $value;
            }
        );
    }
}
