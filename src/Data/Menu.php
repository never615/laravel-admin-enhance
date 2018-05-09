<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;


use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\Data\Traits\PermissionHelp;
use Mallto\Admin\Traits\ModelTree;

/**
 * Class Menu.
 *
 * @property int $id
 *
 * @method where($parent_id, $id)
 */
class Menu extends Model
{
    use PermissionHelp, AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }

//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
    protected $guarded = [];
    protected $fillable = [];


    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.menu_table'));

        parent::__construct($attributes);
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_menu_table');

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

        $tempMenus = \DB::select("with recursive tab as (
                   select * from admin_menu where id = $this->parent_id
                   union all
                   select s.* from admin_menu as s inner join tab on tab.parent_id = s.id
                )
           select * from tab");


        $menus = json_decode(json_encode($tempMenus), true);

        return $menus;
    }


    /**
     * @return array
     */
    public function allNodes(): array
    {
        $orderColumn = DB::getQueryGrammar()->wrap($this->orderColumn);
        $byOrder = $orderColumn.' = 0,'.$orderColumn;
        if (config("admin.auto_menu")) {
            //菜单不跟角色挂钩,只有一份菜单
            //每个人能看到的菜单,由其拥有的权限决定
            //如果是管理员,返回所有菜单;如果是其他账号,返回相应菜单
            if (Auth::guard("admin")->user()->isOwner()) {
                return static::orderByRaw($byOrder)->get()->toArray();
            } else {
                //用来保存用户拥有的所有权限

                $permissions = Auth::guard("admin")->user()->allPermissions();

                $userPermissions = $this->withSubPermissions($permissions);
                $userPermissionSlugs = array_pluck($userPermissions, "slug");

                $tempPermissionSlugs = $userPermissionSlugs;

                foreach ($userPermissionSlugs as $userPermissionSlug) {
                    if (!str_contains($userPermissionSlug, ".")) {
                        $tempPermissionSlugs[] = $userPermissionSlug.".index";
                    }
                }

                $userPermissionSlugs = $tempPermissionSlugs;

                $menus = new Collection();
                //任何人都可以看到控制面板菜单
                $menus = $menus->merge(static::where("uri", "dashboard")->get());
                //查询权限对应的菜单
                $menus = $menus->merge(static::whereIn("uri", $userPermissionSlugs)->get());


                $tempMenus = $menus->toArray();

                //查出来的菜单如果有父菜单也要返回,直到parent_id为0
                foreach ($menus as $item) {
                    $tempMenus = array_merge($tempMenus, $item->parentMenu());
                }

                $uniqueTempArray = [];
                $tempMenus = array_filter($tempMenus, function ($menu) use (&$uniqueTempArray) {
                    if (!in_array($menu["id"], $uniqueTempArray)) {
                        $uniqueTempArray[] = $menu["id"];
                        return true;
                    }else{
                        return false;
                    }
                });


                $result = array_sort($tempMenus, $this->orderColumn);

                return $result;
            }
        } else {
            return static::with('roles')->orderByRaw($byOrder)->get()->toArray();
        }
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
