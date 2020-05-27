<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

class OperationLog extends \Encore\Admin\Auth\Database\OperationLog
{

    protected $fillable = [];

    protected $guarded = [];


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

}
