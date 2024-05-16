<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Traits\BaseModel;
use Mallto\Admin\SubjectUtils;
use Mallto\Admin\SubjectConfigConstants;

class ImportSetting extends BaseModel
{

    public $selectName = "name";

    public $selectId = "import_handler";


    public function records()
    {
        return $this->hasMany(ImportRecord::class, "import_handler", "import_handler");
    }

    public function templateUrl(): Attribute
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
}
