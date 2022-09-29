<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class ImportRecord extends BaseModel
{

    const STATUS = [
        "not_start"         => "未开始",
        "success"           => "全部导入成功",
        "processing"        => "进行中",
        "failure"           => "导入失败",
        "partially_failure" => "部分失败",
        "finish"            => "导入结束",
    ];

    protected $casts = [
        'extra' => 'array',
    ];


    public function setting()
    {
        return $this->belongsTo(ImportSetting::class, "import_handler", "import_handler");
    }

}
