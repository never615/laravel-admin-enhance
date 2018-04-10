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
    use AdminBuilder, ModelTree {
        ModelTree::boot as treeBoot;
    }

//    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri'];
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

        $this->setTable(config('admin.database.menu_table'));

        parent::__construct($attributes);
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles() : BelongsToMany
    {
        $pivotTable = config('admin.database.role_menu_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id');
    }


    public function parentMenu()
    {
        $menus = new Collection();
        if ($this->parent_id != 0) {
            $tempMenu = static::where("id", $this->parent_id)->get();
            $menus = $menus->merge($tempMenu);
            foreach ($tempMenu as $item) {
                $menus = $menus->merge($item->parentMenu());
            }
        }

        return $menus;
    }


    /**
     * @return array
     */
    public function allNodes() : array
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
                $userPermissions = new Collection();

                $permissions = Auth::guard("admin")->user()->allPermissions();

                foreach ($permissions as $permission) {
                    //查询权限的所有子权限
                    $userPermissions = $userPermissions->merge($permission->subPermissions());
                }

                $userPermissions = $userPermissions->merge($permissions);

                $userPermissionSlugs = $userPermissions->pluck('slug');

                $tempPermissionSlugs = $userPermissionSlugs;

                foreach ($userPermissionSlugs as $userPermissionSlug) {
                    if (!str_contains($userPermissionSlug, ".")) {
                        $tempPermissionSlugs[] = $userPermissionSlug.".index";
                    }
                }

                $userPermissionSlugs = $tempPermissionSlugs;

                $menu = new Collection();
                //任何人都可以看到控制面板菜单
                $menu = $menu->merge(static::where("uri", "dashboard")->get());
                //查询权限对应的菜单
                $menu = $menu->merge(static::whereIn("uri", $userPermissionSlugs)->get());


                $tempMenu = $menu;

                //查出来的菜单如果有父菜单也要返回,直到parent_id为0
                foreach ($menu as $item) {
                    $tempMenu = $tempMenu->merge($item->parentMenu());
                }
//                $result = $tempMenu->sortBy($orderColumn)->toArray();
                $result = $tempMenu->sortBy($this->orderColumn)->toArray();

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
