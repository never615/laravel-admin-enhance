<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class SubjectSetting extends BaseModel
{

    public $casts = [
        'front_column'          => 'json',
        'file_type_column'      => 'json',
        'public_configs'        => 'json',
        'private_config'        => 'json',
        'subject_owner_configs' => 'json',
    ];

    const ALLOW_PAY_TYPE = [
        'wechat_pay' => '微信支付',
        'union_pay'  => '银联支付',
        'ali_pay'    => '支付宝支付',
    ];


    public function subjectConfigs()
    {
        return $this->hasMany(SubjectConfig::class, 'subject_id', 'subject_id');
    }

}
