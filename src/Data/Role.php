<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\DynamicData;

class Role extends \Encore\Admin\Auth\Database\Role
{

    use DynamicData;

    protected $fillable = [];

    protected $guarded = [];

}
