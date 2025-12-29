<?php

namespace Mallto\Admin\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mallto\Admin\Data\Traits\DynamicData;

class FrontRole extends Model
{
    protected $table = 'front_roles';


    use DynamicData;

    protected $fillable = [];

    protected $guarded = [];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminApiPermission::class, 'front_role_api_permissions', 'role_id', 'permission_id');
    }

    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(FrontAdminUser::class, 'front_role_admin_user', 'role_id', 'admin_user_id');
    }
}
