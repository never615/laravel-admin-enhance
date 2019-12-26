<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 20/07/2017
 * Time: 11:30 AM
 */

namespace Mallto\Admin\Data\Traits;

use Mallto\Admin\Data\Administrator;

/**
 * Trait AdminUserTrait
 *
 * @deprecated
 * @package Mallto\Admin\Data\Traits
 */
trait AdminUserTrait
{

    public function adminUser()
    {
        return $this->belongsTo(Administrator::class, "admin_user_id");
    }
}
