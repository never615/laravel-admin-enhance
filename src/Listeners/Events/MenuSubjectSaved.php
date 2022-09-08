<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners\Events;

use Illuminate\Queue\SerializesModels;

/**
 * 生成操作日志事件
 */
class MenuSubjectSaved
{

    use SerializesModels;

    public $subjectId;


    public function __construct($subjectId)
    {
        $this->subjectId = $subjectId;
    }

}
