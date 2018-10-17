<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;


use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Schema;
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


    public function scopeSelectSourceDatas()
    {
        if (Admin::user()->isOwner() && Schema::hasColumn($this->getTable(), 'subject_id')) {
            return static::dynamicData()
                ->select(\DB::raw("name||subject_id as name,module_slug"))->pluck("name", "module_slug");
        } else {
            return static::dynamicData()->pluck("name", "module_slug");
        }
    }


}
