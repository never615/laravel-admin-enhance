<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;


use Mallto\Admin\Data\Traits\BaseModel;


class ImportSetting extends BaseModel
{

    public function records()
    {
        return $this->hasMany(ImportRecord::class, "module_slug", "module_slug");
    }


    public function getTemplateUrlAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix").$value;
    }


}
