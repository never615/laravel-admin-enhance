<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Traits\BaseModel;
use Mallto\Admin\SubjectUtils;
use Mallto\Mall\SubjectConfigConstants;

class ImportSetting extends BaseModel
{

    public $selectName = "name";

    public $selectId = "module_slug";


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

        return config("app.file_url_prefix") . $value;
    }


    public function scopeSelectSourceDatas($query)
    {
        return $query->selectSourceDatas2()
            ->pluck($this->selectName, $this->selectId);
    }


    public function scopeSelectSourceDataBySubject($query)
    {
        $query = $query->selectSourceDatas2();

        if ( ! AdminUtils::isOwner()) {
            $ids = SubjectUtils::getConfigByOwner(SubjectConfigConstants::OWNER_CONFIG_IMPORT_MODULE);
            if ($ids) {
                $query = $query->whereIn($this->selectId, $ids);
            }
        }

        return $query->pluck($this->selectName, $this->selectId);
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

}
