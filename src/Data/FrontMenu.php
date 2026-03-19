<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Encore\Admin\Traits\AdminBuilder;
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

    public function getTitleAttribute($value)
    {
        $isOwner = AdminUtils::isOwner();

        if ($isOwner && $this->sub_title) {
            return $value . "-" . $this->sub_title;
        } else {
            return $value;
        }
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
                        ->select("id", "uri", "parent_id", "path", "order", DB::raw("COALESCE($localizedTitle, title) as title"))
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
//        Log::debug("获取前端菜单");
        $orderColumn = DB::getQueryGrammar()->wrap($this->orderColumn);
        $byOrder = $orderColumn . ' = 0,' . $orderColumn;

        $adminUser = null;
        if ($this->adminUser) {
            $adminUser = $this->adminUser;
        } else {
            $adminUser = Auth::guard("admin_api")->user();
        }

        $baseSubject = $adminUser->subject->baseSubject();

        $result = null;
        $language = RequestUtils::getLan();
        $localizedTitle = $language ? "{$language}_title" : 'title';

        if ($adminUser->isOwner()) {
            $result = static::orderByRaw($byOrder)->get()->toArray();
        } else {
            // 为非管理员用户添加语言标识到缓存键中，确保语言切换时菜单能正确更新
//            $locale = app()->getLocale();
            $cacheMenuKey = "front_menu_" . $adminUser->id . '_' . $language;

            $result = Cache::get($cacheMenuKey);
//            $result = null;

//            Log::debug("从缓存中获取前端菜单", ["cacheMenuKey" => $cacheMenuKey, "result" => $result]);
            if ($result) {
                return $this->filterMenuAttributes($result);
            }

            // 获取用户的所有前端权限
            $permissions = $adminUser->allFrontPermissions();

//            Log::debug('用户的前端权限', ['permissions' => $permissions]);
            $userPermissions = $this->withSubFrontPermissions($permissions);
            $userPermissionSlugs = array_pluck($userPermissions, "slug");

            // 处理权限 slug 中的 admin_api. 前缀，转换为菜单 uri
            $userPermissionSlugs = array_map(function ($slug) {
                return str_replace('admin_api.', '', $slug);
            }, $userPermissionSlugs);

//            Log::debug('用户的前端权限 Slugs', ['userPermissionSlugs' => $userPermissionSlugs]);

            // 基于权限查询菜单
            $menus = static::whereIn("uri", $userPermissionSlugs)
                ->select('id',
                    'uri',
                    'parent_id',
                    'path',
                    'order',
                    DB::raw("COALESCE($localizedTitle, title) as title")
                )
                ->get();

//            Log::debug('用户拥有权限的菜单', ['menus' => $menus]);

            $tempMenus = $this->withSubMenus($menus);

            // 查出来的菜单如果有父菜单也要返回,直到parent_id为0
            foreach ($menus as $item) {
                $tempMenus = array_merge($tempMenus, $item->parentMenu());
            }

            // 过滤保证唯一
            $uniqueTempArray = [];
            $tempMenus1 = array_filter($tempMenus, function ($menu) use (&$uniqueTempArray) {
                if (!in_array($menu["id"], $uniqueTempArray)) {
                    $uniqueTempArray[] = $menu["id"];
                    return true;
                } else {
                    return false;
                }
            });

            // 处理动态链接
            $uniqueTempArray2 = [];
            $tempMenus = array_map(function ($menu) use (&$uniqueTempArray2, $baseSubject) {
                if (!in_array($menu["id"], $uniqueTempArray2)) {
                    $uniqueTempArray2[] = $menu["id"];

                    if (isset($menu['uri']) && starts_with($menu['uri'], 'http://')) {
                        $uriKey = str_replace('http://', '', $menu['uri']);
                        //替换动态链接
                        $uriValue = SubjectUtils::getDynamicKeyConfigByOwner($uriKey, $baseSubject, $menu['uri']);
                        $menu['uri'] = $uriValue;
                    }

                    return $menu;
                }
                return null;
            }, $tempMenus1);

            // 过滤掉 null 值
            $tempMenus = array_filter($tempMenus);

            // 排序
            $result = array_sort($tempMenus, $this->orderColumn);

            // 缓存结果
            CacheUtils::putMenu($cacheMenuKey, $result);

            $cacheMenuKeys = Cache::get(CacheConstants::CACHE_FRONT_MENU_KEYS, []);
            $cacheMenuKeys[] = $cacheMenuKey;

            CacheUtils::putFrontMenuKeys($cacheMenuKeys);
        }

        return $this->filterMenuAttributes($result);
    }

    /**
     * 过滤菜单属性，只保留指定的字段
     * 保留字段: id, parent_id, order, title, icon, uri, en_title, tc_title, children
     *
     * @param array $menus
     * @return array
     */
    private function filterMenuAttributes(array $menus): array
    {
        $allowedAttributes = ['id', 'parent_id', 'order', 'title', 'icon', 'uri', 'en_title', 'tc_title', 'children'];

        return array_map(function ($menu) use ($allowedAttributes) {
            $filteredMenu = [];
            foreach ($allowedAttributes as $attr) {
                if (isset($menu[$attr])) {
                    $filteredMenu[$attr] = $menu[$attr];
                }
            }
            return $filteredMenu;
        }, $menus);
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
        $localizedTitle = $language ? "{$language}_title" : 'title';


        $ids = $menus->pluck("id")->toArray();
        $ids = array_map(function ($id) {
            return "%." . $id . ".%";
        }, $ids);
        $ids = implode(",", $ids);
        $ids = "('{" . $ids . "}')";
        $tempMenus = FrontMenu::query()->whereRaw("path like any $ids");

        $tempMenus = $tempMenus->select(
            'id',
            'uri',
            'parent_id',
            'path',
            'order',
            DB::raw("COALESCE($localizedTitle, title) as title"));
        $tempMenus = $tempMenus->get()->toArray();
        return array_merge($tempMenus, $menus->toArray());
    }


    /**
     *
     * 获取一组前端权限的所有子权限,带着原来的这一组权限一起返回
     *
     * @param $permissions
     *
     * @return array
     */
    public function withSubFrontPermissions($permissions)
    {
        $ids = $permissions->pluck("id")->toArray();
        $ids = array_map(function ($id) {
            return "%." . $id . ".%";
        }, $ids);
        $ids = implode(",", $ids);
        $ids = "('{" . $ids . "}')";

        $tempPermissions = AdminApiPermission::whereRaw("path like any $ids")
            ->get()
            ->toArray();

        return array_merge($tempPermissions, $permissions->toArray());
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
