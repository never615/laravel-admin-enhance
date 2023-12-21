<?php

namespace Mallto\Admin\Data;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mallto\Admin\Traits\ModelTree;

class AdminApiPermission extends Model
{

    use ModelTree, AdminBuilder;

//    protected $fillable = [];

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

//        $this->setTable(config('admin.database.permissions_table'));

        $this->setTitleColumn("name");

        parent::__construct($attributes);
    }


//    /**
//     * 获取拥有该权限的全部主体
//     *
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
//     */
//    public function subjects()
//    {
//        return $this->belongsToMany(Subject::class, "subject_permissions", 'permission_id', 'subject_id');
//    }


    /**
     * Permission belongs to many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
//        $pivotTable = config('admin.database.role_permissions_table');
        $pivotTable = 'admin_role_api_permissions';

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
        return AdminApiPermission::where("path", "like", "%." . $this->id . ".%")
            ->orWhere("id", $this->id)
            ->get()
            ->toArray();
    }


    /**
     * 获取该权限的所有长辈权限
     *
     * 不包含自身
     *
     * @return Collection
     */
    public function elderPermissions()
    {
        if (!empty($this->path)) {
            $parentIds = explode(".", trim($this->path, "."));
            if (!empty($parentIds)) {
                return AdminApiPermission::whereIn("id", $parentIds)
                    ->get();
            }
        }

        return new Collection();
    }


    /**
     * 通过递归,效率差点,用这个方法补全没有path的数据,才能使用上面的elderPermissions方法
     *
     * @return Collection
     */
    public function elderPermissions2()
    {
        $temps = \DB::select("with recursive tab as (
                 select * from admin_api_permissions where id = $this->parent_id
                  union all
                  select s.* from admin_api_permissions as s inner join tab on tab.parent_id = s.id
                )
           select * from tab order by id");

        return new Collection(json_decode(json_encode($temps), true));
    }


    /**
     * Build options of select field in form.
     *
     * @param array $nodes
     * @param int $parentId
     * @param string $prefix
     *
     * @param bool $defaultBlack
     *
     * @return array
     */
    protected function buildSelectOptions(
        array $nodes = null,
              $parentId = 0,
              $prefix = '',
              $defaultBlack = true
    )
    {
        if ($defaultBlack) {
            $prefix = $prefix ?: str_repeat('&nbsp;', 6);
        } else {
            $prefix = $prefix ?: str_repeat('&nbsp;', 2);
        }

        $options = [];

        if ($nodes === null) {
            $nodes = $this->allNodes();
        }

        //\Log::debug(json_encode($nodes));

        $parentId = (array)$parentId;

        foreach ($nodes as $node) {
            if ($defaultBlack) {
                $node[$this->titleColumn] = $prefix . '&nbsp;' . $node[$this->titleColumn];
            }

//            if ($node[$this->parentColumn] == $parentId) {
            if (in_array($node[$this->parentColumn], $parentId)) {
                $children = $this->buildSelectOptions($nodes, $node[$this->getKeyName()], $prefix . $prefix);

                if ($children) {
                    $options[$node[$this->getKeyName()]] = '<b>' . $node[$this->titleColumn] . '</b>';
                } else {
                    $options[$node[$this->getKeyName()]] = $node[$this->titleColumn];
                }

                if ($children) {
                    $options += $children;
                }

            }
        }

        return $options;
    }

}
