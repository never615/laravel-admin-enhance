<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class SubjectConfig extends BaseModel
{

    const TYPE = [
        'public'  => '公共配置',
        'private' => '私有配置',
        'front'   => '前端配置',
    ];

}
