<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;


use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\Data\Traits\DynamicData;
use Mallto\Admin\Data\Traits\HasPermissions;
use Mallto\Admin\Data\Traits\SelectSource;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable, AdminBuilder, HasPermissions, DynamicData, HasMultiAuthApiTokens, SelectSource;

    protected $fillable = [
        'username',
        'password',
        'name',
        'avatar',
        "subject_id",
        "adminable_id",
        "adminable_type",
        "manager_subject_ids",
    ];

    protected $casts = [
        'extra'               => 'array',
        'manager_subject_ids' => "array",
        'openid'              => 'array', //用户微信信息
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function adminable()
    {
        return $this->morphTo();
    }

    public function groups()
    {
        return $this->belongsToMany(AdminUserGroup::class, "admin_user_group_users",
            'user_id', 'group_id');
    }

}
