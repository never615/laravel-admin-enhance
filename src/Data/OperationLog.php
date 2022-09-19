<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\DynamicData;

class OperationLog extends \Encore\Admin\Auth\Database\OperationLog
{

    use DynamicData;

    protected $fillable = [];

    protected $guarded = [];


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

}
