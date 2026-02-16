<?php

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\Permission;

/**
 * 生成权限的seeder基础方法
 *
 *
 * 权限校验设计说明:
 * 1.返回给前端的菜单会通过账号角色配置的权限自动匹配菜单返回,需要权限标识符合菜单标识符一致.
 * (这样设计避免了给了角色配置一遍权限然后还需要配置一遍菜单的情况)
 * 2. 权限标识又和接口路由名保持一致,这样就会根据登录用户拥有的权限决定是否要通过对应的路由.
 * 3. 所以最后就是对于表格表单模块来说:   菜单标识=权限标识=接口路由标识一致; 非表格表单模块就是: 菜单标识=权限标识
 *
 *
 * 菜单标识设计思路是:
 * 1.如果普通的表格表单模块对应的是后端一个表的增删改查,菜单名就是模块名(表名)的单数
 * (用单数是因为历史原因,最初 php 管理端路由用复数怕和前端接口路由重复,后续解决的重复问题,但是没有用回复数了).
 * 2.否则的话,菜单名就是 admin_map 开头的. 比如实时定位用admin_map_position.
 * (不加 admin_map 开头可能和现有路由名重复,使用约定的方式解决,相比使用设计的方式分开菜单和权限标识,
 * 可以减少开发时间和后续维护简单一些)
 *
 * Created by PhpStorm.
 * User: never615
 * Date: 24/04/2017
 * Time: 4:51 PM
 */
trait SeederMaker
{


    protected $routeNames = [
        "index" => "查看",  //列表页/详情页/show
        "create" => "创建/修改", //创建页/保存/修改
        "destroy" => "删除", //删除权限
        "export" => "导出", //导出权限
    ];

    protected $model = Permission::class;

    protected $routeNamePrefix = '';

    // 是否全局生成子权限
    protected $globalSub = true;


    /**
     * @param      $name ,权限名
     * @param      $slug ,权限标识
     * @param bool $sub ,是否生成子权限
     * @param int $parentId ,父权限id
     * @param bool $closeDelete ,是否关闭创建子权限之`删除`权限
     * @param bool $common ,是否是所有主体都默认有的公共权限
     * @param bool $closeCreate ,是否关闭创建子权限之`创建/修改`权限
     * @param null $routeNames
     * @param bool $force ,存在同名权限,则删除
     *
     * @return int
     * @throws \Exception
     */
    public function createPermissions(
        $name,
        $slug,
        $sub = true,
        $parentId = 0,
        $closeDelete = false,
        $common = false,
        $closeCreate = false,
        $routeNames = null,
        $force = false
    )
    {
        if ($this->isGlobalSub() === false) {
            $sub = false;
        }

        if ($this->getRouteNamePrefix()) {
            $slug = $this->getRouteNamePrefix() . '.' . $slug;
        }


//        $this->order = $this->order ?? Permission::max("order");
        $this->order = $this->model::max("order");

        $path = "";
        $parentPermission = $this->model::find($parentId);
        if ($parentPermission) {
            if (!empty($parentPermission->path)) {
                $path = $parentPermission->path . $parentPermission->id . ".";
            } else {
                $path = "." . $parentPermission->id . ".";
            }
        }

        try {
            $temp = $this->model::updateOrCreate(
                [
                    "slug" => $slug,
                ],
                [
                    "parent_id" => $parentId,
                    'order' => $this->order += 1,
                    "name" => $name,
                    "common" => $common,
                    "path" => $path,
                ]);
        } catch (\Exception $exception) {
            if ($force) {

                $this->model::where("name", $name)->delete();

                $temp = $this->model::updateOrCreate(
                    [
                        "slug" => $slug,
                    ],
                    [
                        "parent_id" => $parentId,
                        'order' => $this->order += 1,
                        "name" => $name,
                        "common" => $common,
                        "path" => $path,
                    ]);
            } else {
                throw  $exception;
            }
        }

        $parentId = $temp->id;

        $path = "";
        $parentPermission = $this->model::find($parentId);
        if ($parentPermission) {
            if (!empty($parentPermission->path)) {
                $path = $parentPermission->path . $parentPermission->id . ".";
            } else {
                $path = "." . $parentPermission->id . ".";
            }
        }

        if ($sub) {
            if (!$routeNames) {
                $routeNames = $this->routeNames;
            }

            if ($closeDelete) {
                unset($routeNames["destroy"]);
            }

            if ($closeCreate) {
                unset($routeNames["create"]);
            }

            foreach ($routeNames as $routeName => $permissionName) {
                $this->model::updateOrCreate([
                    "slug" => $slug . "." . $routeName,
                ], [
                    'parent_id' => $parentId,
                    'order' => $this->order += 1,
                    "name" => $name . $permissionName,
                    "path" => $path,

                ]);
            }
        }

        return $parentId;
    }


    public function delete($slug, $sub = true, $model = null)
    {
        $tempModel = $this->model;
        if ($model && !is_bool($model)) {
            $tempModel = $model;
        }

        if ($this->routeNamePrefix) {
            $slug = $this->routeNamePrefix . '.' . $slug;
        }
        $tempModel::query()->where('slug', $slug)->delete();

        if ($sub) {
            $routeNames = $this->routeNames;

            foreach ($routeNames as $routeName => $permissionName) {
                $tempModel::query()->where('slug', $slug . "." . $routeName)->delete();
            }
        }

    }


    public function __get($name)
    {
        if ($name == "order") {
            if (isset($this->order)) {
                return $this->order;
            } else {
                return 10000;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model): void
    {
        $this->model = $model;
    }

    public function getRouteNamePrefix(): string
    {
        return $this->routeNamePrefix;
    }

    public function setRouteNamePrefix(string $routeNamePrefix): void
    {
        $this->routeNamePrefix = $routeNamePrefix;
    }

    public function isGlobalSub(): bool
    {
        return $this->globalSub;
    }

    public function setGlobalSub(bool $globalSub): void
    {
        $this->globalSub = $globalSub;
    }


}
