<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\CacheConstants;
use Mallto\Admin\CacheUtils;
use Mallto\Admin\SubjectUtils;
use Mallto\Admin\Traits\ModelTree;
use Mallto\Tool\Utils\RequestUtils;

/**
 * Class Menu.
 *
 * @property int $id
 *
 * @method where($parent_id, $id)
 */
class FrontMenu extends Model
{

    use  AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }

//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
    protected $guarded = [];

    protected $fillable = [];
    /**
     * @var null
     */
    private $adminUser;


    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [], $adminUser = null)
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

//        $this->setTable(config('admin.database.menu_table'));

        $this->adminUser = $adminUser;
        parent::__construct($attributes);
    }

    public function title(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                $isOwner = AdminUtils::isOwner();

                if ($isOwner && $this->sub_title) {
                    return $value . "-" . $this->sub_title;
                } else {
                    return $value;
                }
            }
        );
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = 'admin_role_front_menu';

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id');
    }


    /**
     * 获取菜单的父菜单
     *
     * @return array
     */
    public function parentMenu()
    {
        if (!empty($this->path)) {
            $parentIds = explode(".", trim($this->path, "."));
            if (!empty($parentIds)) {
                $language = RequestUtils::getLan();
                if ($language) {
                    $localizedTitle = "{$language}_title";
                    return FrontMenu::query()
                        ->select("id", "uri", "parent_id", "path", "order",DB::raw("COALESCE($localizedTitle, title) as title"))
                        ->whereIn("id", $parentIds)
                        ->get()
                        ->toArray();
                } else {
                    return FrontMenu::whereIn("id", $parentIds)
                        ->get()
                        ->toArray();
                }
            }
        }

        return [];
    }


    public function parentMenu2()
    {
        $tempMenus = \DB::select("with recursive tab as (
                   select * from admin_front_menu where id = $this->parent_id
                   union all
                   select s.* from admin_front_menu as s inner join tab on tab.parent_id = s.id
                )
           select * from tab order by id");

        $menus = json_decode(json_encode($tempMenus), true);

        return $menus;
    }


    /**
     * @return array
     */
    public function allNodes(): array
    {
        $orderColumn = DB::getQueryGrammar()->wrap($this->orderColumn);
        $byOrder = $orderColumn . ' = 0,' . $orderColumn;

        $adminUser = null;
        if ($this->adminUser) {
            $adminUser = $this->adminUser;
        } else {
            $adminUser = Auth::guard("admin_api")->user();
        }

        $baseSubject = $adminUser->subject->baseSubject();

        if ($adminUser->isOwner()) {
            return static::orderByRaw($byOrder)->get()->toArray();
        } else {
//            $result = Cache::get("front_menu_" . $adminUser->id);
//            if ($result) {
//                return $result;
//            }

//            $menus = new Collection();

            $menus = $adminUser->frontMenus();

            $tempMenus = $this->withSubMenus($menus);
//            $tempMenus = $menus->toArray();

            //查出来的菜单如果有父菜单也要返回,直到parent_id为0
            foreach ($menus as $item) {
                $tempMenus = array_merge($tempMenus, $item->parentMenu());
            }


            //过滤保证唯一
            $uniqueTempArray = [];
            $tempMenus1 = array_filter($tempMenus, function ($menu) use (&$uniqueTempArray, $baseSubject) {
                if (!in_array($menu["id"], $uniqueTempArray)) {
                    $uniqueTempArray[] = $menu["id"];
                    return true;
                } else {
                    return false;
                }
            });

            $uniqueTempArray2 = [];
            $tempMenus = array_map(function ($menu) use (&$uniqueTempArray2, $baseSubject) {
                if (!in_array($menu["id"], $uniqueTempArray2)) {
                    $uniqueTempArray2[] = $menu["id"];

                    if (starts_with($menu['uri'], 'http://')) {
                        $uriKey = str_replace('http://', '', $menu['uri']);
                        //替换动态链接
                        $uriValue = SubjectUtils::getDynamicKeyConfigByOwner($uriKey, $baseSubject, $menu['uri']);
                        $menu['uri'] = $uriValue;
                    }

                    return $menu;
                }
            }, $tempMenus1);

            //排序
            $result = array_sort($tempMenus, $this->orderColumn);

            $cacheMenuKey = "front_menu_" . $adminUser->id;
            CacheUtils::putMenu($cacheMenuKey, $result);

            $cacheMenuKeys = Cache::get(CacheConstants::CACHE_MENU_KEYS, []);
            $cacheMenuKeys[] = $cacheMenuKey;

            CacheUtils::putMenuKeys($cacheMenuKeys);

            return $result;
        }
    }


    /**
     *
     * 获取一组所有子菜单
     *
     * @param $menus
     *
     * @return array
     */
    public function withSubMenus($menus)
    {
        $language = RequestUtils::getLan();

        $ids = $menus->pluck("id")->toArray();
        $ids = array_map(function ($id) {
            return "%." . $id . ".%";
        }, $ids);
        $ids = implode(",", $ids);
        $ids = "('{" . $ids . "}')";
        $tempMenus = FrontMenu::query()->whereRaw("path like any $ids");

        if ($language) {
            $localizedTitle = "{$language}_title";
            $tempMenus = $tempMenus->select('id',
                'uri',
                'parent_id',
                'path',
                'order',
                DB::raw("COALESCE($localizedTitle, title) as title"));
        } else {
            $tempMenus = $tempMenus->select('id', 'title', 'uri', 'parent_id', 'path', 'order');
        }
        $tempMenus=$tempMenus->get()->toArray();
        return array_merge($tempMenus, $menus->toArray());

//        $tempPermissions = [];
//        foreach ($permissions as $permission) {
//            //查询权限的所有子权限
//            $tempPermissions = array_merge($tempPermissions, $permission->subPermissions());
//        }
//
//        return array_merge($tempPermissions, $permissions->toArray());
    }


    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function boot()
    {
        static::treeBoot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }
}
