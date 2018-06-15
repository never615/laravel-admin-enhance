<?php

namespace Mallto\Admin\Data;


use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\Traits\ModelTree;

class Permission extends Model
{
    use ModelTree, AdminBuilder;
    protected $fillable = [];

    protected $guarded = [];


    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.permissions_table'));

        $this->setTitleColumn("name");


        parent::__construct($attributes);
    }

    /**
     * 获取拥有该权限的全部主体
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, "subject_permissions", 'permission_id', 'subject_id');
    }

    /**
     * Permission belongs to many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        $pivotTable = config('admin.database.role_permissions_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'permission_id', 'role_id');
    }

    /**
     * 查询对应权限的所有子权限
     *
     * 包含自身
     *
     * @return array
     */
    public function subPermissions()
    {

        $temps = \DB::select("with recursive tab as (
                   select * from admin_permissions where id = $this->id
                   union all
                   select s.* from admin_permissions as s inner join tab on tab.id = s.parent_id
                )
           select * from tab");

        return \Qiniu\json_decode(\GuzzleHttp\json_encode($temps), true);


//        $tempPermissions = new Collection();
//        $permissions = static::where("parent_id", $this->id)->get();
//        $tempPermissions = $tempPermissions->merge($permissions);
//        foreach ($permissions as $permission) {
//            $tempPermissions = $tempPermissions->merge($permission->subPermissions());
//        }
//
//        return $tempPermissions;
    }

    /**
     * 获取该权限的所有长辈权限
     *
     * 不包含自身
     * @return Collection
     */
    public function elderPermissions()
    {

        $temps = \DB::select("with recursive tab as (
                 select * from admin_permissions where id = $this->parent_id
                  union all
                  select s.* from admin_permissions as s inner join tab on tab.parent_id = s.id
                )
           select * from tab");

        return new Collection(\Qiniu\json_decode(\GuzzleHttp\json_encode($temps), true));


//        $tempPermissions = new Collection();
//
//        $permission = static::find($this->parent_id);
//        if ($permission) {
//            $tempPermissions = $tempPermissions->push($permission);
//            $temp = $permission->elderPermissions();
//            if ($temp->count() > 0) {
//                $tempPermissions = $tempPermissions->merge($temp);
//            }
//        }
//
//        return $tempPermissions;

    }


}
