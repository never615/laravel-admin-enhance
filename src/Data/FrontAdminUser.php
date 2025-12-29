<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Mallto\Admin\Data\Traits\DynamicData;
use Mallto\Admin\Data\Traits\HasFrontPermissions;
use Mallto\Admin\Data\Traits\SelectSource;

class FrontAdminUser extends Authenticatable
{
    use HasApiTokens;
    use HasFrontPermissions;
    use DynamicData;
    use SelectSource;

    protected $table = 'front_admin_users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(FrontRole::class, 'front_role_admin_user', 'admin_user_id', 'role_id');
    }
}

