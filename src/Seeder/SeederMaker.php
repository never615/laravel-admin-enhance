<?php

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\Permission;

/**
 * 生成权限的seeder基础方法
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


    public function delete($slug, $model = null, $sub = false)
    {
        $tempModel = $model ?? $this->model;
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


}
