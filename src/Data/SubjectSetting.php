<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class SubjectSetting extends BaseModel
{

    protected $guarded = [];

    protected $casts = [
        'union_pay_setting' => 'array',
    ];
}
