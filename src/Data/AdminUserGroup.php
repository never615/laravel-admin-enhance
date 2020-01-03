<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Mallto\Admin\Data\Traits\BaseModel;

class AdminUserGroup extends BaseModel
{

    protected $table = 'admin_user_groups';


    public function users()
    {
        return $this->belongsToMany(Administrator::class, "admin_user_group_users",
            'group_id', 'user_id');
    }
}
