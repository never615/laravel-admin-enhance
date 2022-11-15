<?php
/*
 * Copyright (c) 2022. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Model;

/**
 * User: never615 <never615.com>
 * Date: 2022/11/16
 * Time: 1:30 AM
 */
class Session extends Model
{

    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(Administrator::class);
    }
}
