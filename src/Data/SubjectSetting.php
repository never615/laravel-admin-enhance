<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class SubjectSetting extends BaseModel
{

    //s_s_[subject_id]_
    const CACHE_KEY = 's_s_';


    public static function getCacheKey($subjectId)
    {
        return self::CACHE_KEY . $subjectId . '_';
    }


    public $casts = [
        'front_column'          => 'json',
        'file_type_column'      => 'json',
        'public_configs'        => 'json',
        'private_configs'       => 'json',
        'subject_owner_configs' => 'json',
        'allow_pay_type'        => 'json',
    ];


    public function subjectConfigs()
    {
        return $this->hasMany(SubjectConfig::class, 'subject_id', 'subject_id');
    }

}
