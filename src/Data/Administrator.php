<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Encore\Admin\Auth\Database\HasPermissions;
use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Mallto\Admin\Data\Traits\DynamicData;
use Mallto\Admin\Data\Traits\HasPermissions2;
use Mallto\Admin\Data\Traits\SelectSource;
use Mallto\Tool\Utils\RequestUtils;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends \Encore\Admin\Auth\Database\Administrator
{

    use HasApiTokens, Authenticatable, AdminBuilder, HasPermissions,
        DynamicData, SelectSource, HasPermissions2 {
        HasPermissions2::can insteadof HasPermissions;
    }

    const STATUS = [
        "normal" => "正常",
        "forbidden" => "禁用",
    ];

    //管理端用来选择账号类型的select
    const ADMINABLE_TYPE = [
        'subject' => '主体',
    ];

    protected $fillable = [
    ];

    protected $guarded = [];

    protected $casts = [
        'extra' => 'array',
        'manager_subject_ids' => "array",
        'openid' => 'array', //用户微信信息
    ];


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


    public function frontMenus(): Collection
    {
        $language = RequestUtils::getLan();
//        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->merge($this->permissions);
//        return $this->roles()->with('frontMenus')->get()->pluck('frontMenus')->flatten();
//        return $this->roles()->with('frontMenus:id,title,uri,parent_id,path,order')->get()->pluck('frontMenus')->flatten();
        return $this->roles()->with(['frontMenus' => function ($query) use ($language) {
            // 使用本地作用域来添加本地化标题
            if ($language) {
                $localizedTitle = "{$language}_title";
                $query->select('id',
                    'uri',
                    'parent_id',
                    'path',
                    'order',
                    DB::raw("COALESCE($localizedTitle, title) as title"));
            } else {
                $query->select('id', 'title', 'uri', 'parent_id', 'path', 'order');
            }
        }])->get()->pluck('frontMenus')->flatten();
    }
}
