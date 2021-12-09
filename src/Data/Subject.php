<?php

namespace Mallto\Admin\Data;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Traits\ModelTree;

class Subject extends Model
{

    use ModelTree;

    protected $tempChildrenSubjectIds;

    protected $tempParentSubject;

    protected $tempBaseSubject;


    /**
     * Subject constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTitleColumn("name");
    }


    protected $casts = [
        'extra_config'      => 'array',
        'open_extra_config' => 'array',
        'park_notify_third' => 'array',
    ];

    protected $guarded = [
    ];


    public function menus()
    {
        return $this->belongsToMany(Menu::class, "admin_menu_subjects", "subject_id", "admin_menu_id");
    }


    public static function selectSourceDate()
    {
        $isOwner = AdminUtils::isOwner();

        if ($isOwner) {
            return static::dynamicData()
                ->select(DB::raw("name||'-主体id:'||id as name,id"))
                //->orderBy('created_at', 'desc')
                ->pluck("name", "id");
        } else {
            return static::dynamicData()
                //->orderBy('created_at', 'desc')
                ->pluck("name", "id");
        }
    }


    public function getLogoAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (starts_with($value, "http")) {
            return $value;
        }

        return config("app.file_url_prefix") . $value;
    }


    public function subjectAdminUsers()
    {
        return $this->morphMany(Administrator::class, 'adminable');
    }


    public function subjectConfigs()
    {
        return $this->hasMany(SubjectConfig::class);
    }


    /**
     * 获取该主题下所有管理账号,包括店铺账号和主体账号
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adminUsers()
    {
        return $this->hasMany(Administrator::class);
    }


    public function reports()
    {
        return $this->hasMany(Report::class);
    }


    /**
     * 获得该主体拥有的全部权限
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, "subject_permissions", 'subject_id', 'permission_id');
    }


    /**
     * 动态设定查询数据范围
     *
     * 项目拥有者和招商拥有查看全部业务数据的能力
     * 子主体只能查看自己拥有的数据
     *
     * @param $query
     */
    public function scopeDynamicData($query)
    {
        //1.获取当前登录账户属于哪一个主体
        $adminUser = Admin::user();

        //处理数据查看范围
        //如果设置了manager_subject_ids,则优先处理该值
        $managerSubjectIds = $adminUser->manager_subject_ids;
        if ( ! empty($managerSubjectIds)) {
            $tempSubject = new Subject();
            $tempSubjectIds = $managerSubjectIds;

            foreach ($managerSubjectIds as $managerSubjectId) {
                $tempSubjectIds = array_merge($tempSubjectIds,
                    $tempSubject->getChildrenSubject($managerSubjectId));
            }
            $tempSubjectIds = array_unique($tempSubjectIds);
        } else {
            $currentSubject = $adminUser->subject;
            $tempSubjectIds = $currentSubject->getChildrenSubject();
        }

        //3.限定查询范围为所有子主体
        $query->whereIn('id', $tempSubjectIds)
            ->orderBy('id', 'desc');
    }


    /**
     * 获取父类的基主体,一般来说是总公司的身份
     * 即父主体活着自己base属性为true的主体
     *
     * @return $this|mixed|static
     */
    public function baseSubject()
    {
        if ($this->base) {
            return $this;
        } else {
            $baseSubject = null;
            if ( ! empty($this->path)) {
                $parentIds = explode(".", trim($this->path, "."));
                if ( ! empty($parentIds)) {
                    $baseSubject = Subject::whereIn("id", $parentIds)
                        ->where("base", true)
                        ->first();
                }
            }

            return $baseSubject ?: $this;
        }
    }


    /**
     * 获取所有子主体id,包括自身
     *
     * @param string $subjectId
     *
     * @return array
     */
    public function getChildrenSubject($subjectId = null)
    {

        $currentSubjectId = $subjectId ?: $this->id;

        return Subject::where("path", "like", "%." . $this->id . ".%")
            ->orWhere("id", $currentSubjectId)
            ->pluck("id")
            ->toArray();
//
//        $currentSubjectId = $subjectId ?: $this->id;
//        if ($this->tempChildrenSubjectIds && isset($this->tempChildrenSubjectIds[$currentSubjectId])) {
//            return $this->tempChildrenSubjectIds[$currentSubjectId];
//        }
//
//
//        $tempSubjects = DB::select("with recursive tab as (
//                   select * from subjects where id = $currentSubjectId
//                   union all
//                   select s.* from subjects as s inner join tab on tab.id = s.parent_id
//                )
//           select * from tab");
//
//        $idResults = array_pluck($tempSubjects, "id");
//
//        $this->tempChildrenSubjectIds[$currentSubjectId] = $idResults;
//
//        return $idResults;
    }


    /**
     * 获取所有父级主体,不包括自己
     *
     * 一般获取用来进行健壮性检查
     *
     */
    public function getParentSubjectIds()
    {
        $parentIds = explode(".", trim($this->path, "."));
        if ( ! empty($this->path)) {
            if ( ! empty($parentIds)) {
                return Subject::whereIn("id", $parentIds)
                    ->pluck("id")
                    ->toArray();
            }
        }

        return [];
    }


    /**
     * 试试递归计算获取所有父ids
     *
     * @return array
     */
    public function getParentSubjectIds2()
    {
        $currentSubjectId = $this->id;
        if ($this->tempParentSubject && isset($this->tempParentSubject[$currentSubjectId])) {
            return $this->tempParentSubject[$currentSubjectId];
        }

        $tempSubjects = DB::select("with recursive tab as (
                 select * from subjects where id = $currentSubjectId
                  union all
                  select s.* from subjects as s inner join tab on tab.parent_id = s.id
                )
           select * from tab where id != $currentSubjectId order by id ");

        if (empty($tempSubjects)) {
            $idResults = [];
        } else {
            $idResults = array_pluck($tempSubjects, "id");
            $this->tempParentSubject[$currentSubjectId] = $idResults;

        }

        return $idResults;
    }


    /**
     * 判断该主体是否有子主体
     *
     * @return bool
     */
    public function hasChildrenSubject()
    {
        return static::where("parent_id", $this->id)->count() > 0 ? true : false;
    }


    /**
     * Get options for Select field in form.
     *
     * @param array $nodes
     * @param bool  $root         ,是否返回root节点
     * @param bool  $defaultBlack ,是否使用默认的空格大小
     * @param int   $parentId
     *
     * @return \Illuminate\Support\Collection
     */
    public static function selectOptions(
        array $nodes = null,
        $root = true,
        $defaultBlack = true,
        $parentId = 0
    ) {
        $options = (new static())->buildSelectOptions($nodes, $parentId, "", $defaultBlack);

        if ($root) {
            return collect($options)->prepend('Root', 0)->all();
        } else {
            return collect($options)->all();
        }
    }
}
