<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mallto\Admin\Data\Traits\DynamicData;

class Role extends \Encore\Admin\Auth\Database\Role
{

    use DynamicData;

    protected $fillable = [];

    protected $guarded = [];


    /**
     * A role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function apiPermissions(): BelongsToMany
    {
        $pivotTable = 'admin_role_api_permissions';

        $relatedModel = AdminApiPermission::class;

        return $this->belongsToMany($relatedModel, $pivotTable, 'role_id', 'permission_id');
    }

}
