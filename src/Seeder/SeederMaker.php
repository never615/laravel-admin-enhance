<?php

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Permission;

/**
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
    ];

    /**
     * @param      $name ,权限名
     * @param      $slug ,权限标识
     * @param bool $sub ,是否生成子权限
     * @param int $parentId ,父权限id
     * @param bool $closeDelete ,是否关闭创建子权限之`删除`权限
     * @param bool $common ,是否是所有主体都默认有的公共权限
     * @param bool $closeCreate ,是否关闭创建子权限之`创建/修改`权限
     * @return int
     */
    public function createPermissions(
        $name,
        $slug,
        $sub = true,
        $parentId = 0,
        $closeDelete = false,
        $common = false,
        $closeCreate = false
    )
    {
        $temp = Permission::updateOrCreate(
            [
                "slug" => $slug,
            ],
            [
                "parent_id" => $parentId,
                'order' => $this->order += 1,
                "name" => $name,
                "common" => $common,
            ]);

        $parentId = $temp->id;

        if ($sub) {
            $routeNames = $this->routeNames;
            if ($closeDelete) {
                unset($routeNames["destroy"]);
            }

            if ($closeCreate) {
                unset($routeNames["create"]);
            }

            foreach ($routeNames as $routeName => $permissionName) {
                Permission::updateOrCreate([
                    "slug" => $slug . "." . $routeName,
                ], [
                    'parent_id' => $parentId,
                    'order' => $this->order += 1,
                    "name" => $name . $permissionName,

                ]);
            }
        }

        return $parentId;
    }
}
