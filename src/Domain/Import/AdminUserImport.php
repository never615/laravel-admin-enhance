<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Import;

use Illuminate\Validation\Rule;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Traits\AdminPermissionDistribution;
use Mallto\Tool\Exception\ResourceException;

class  AdminUserImport extends BaseImportHandler
{

    use AdminPermissionDistribution;

    public $importMode = 'eachRow';


    /**
     * 获取导入文件期望的列名
     *
     * @return mixed
     */
    public function getExpectKeys()
    {
        return [
            '组织机构',
            '用户名(登录账号)',
            '密码(不写就和登录名一样)',
            '名称',
            '角色名(可空)',
            '手机号(可空)',
            //'组织机构(父级权限)',
            //'组织机构查看',
            //'组织机构创建',
            //'组织机构修改',
            //'组织机构删除',
            //'账户(父级权限)',
            //'账户查看',
            //'账户创建/修改',
            //'账户删除',
            //'角色(父级权限)',
            //'角色查看',
            //'角色创建/修改',
            //'角色删除',
            //'角色导出',
            //'报表(父级权限)',
            //'报表查看',
            //'报表创建/修改',
            //'报表删除',
            //'报表导出',
            //'数据导入(父级权限)',
            //'数据导入查看',
            //'数据导入创建/修改',
            //'数据导入删除',
            //'数据导入导出',
            //'操作日志(父级权限)',
            //'操作日志查看',
            //'操作日志创建/修改',
            //'操作日志删除',
            //'操作日志导出',
            //'Dashboard(父级权限)',
            //'尾箱(父级权限)',
            //'尾箱查看',
            //'尾箱创建/修改',
            //'尾箱删除',
            //'尾箱导出',
            //'尾箱出入库状态盘点(父级权限)',
            //'尾箱出入库状态盘点查看',
            //'尾箱出入库状态盘点导出',
            //'尾箱结余统计(父级权限)',
            //'尾箱结余统计查看',
            //'尾箱结余统计导出',
            //'尾箱区域日志(父级权限)',
            //'尾箱区域日志查看',
            //'尾箱区域日志创建/修改',
            //'尾箱区域日志删除',
            //'尾箱区域日志导出',
            //'尾箱区域报警规则(父级权限)',
            //'尾箱区域报警规则查看',
            //'尾箱区域报警规则创建/修改',
            //'尾箱区域报警规则删除',
            //'尾箱区域报警规则导出',
            //'尾箱区域报警记录(父级权限)',
            //'尾箱区域报警记录查看',
            //'尾箱区域报警记录删除',
            //'尾箱区域报警记录导出',
            //'尾箱区域报警记录处理',
            //'尾箱拆除报警规则(父级权限)',
            //'尾箱拆除报警规则查看',
            //'尾箱拆除报警规则创建/修改',
            //'尾箱拆除报警规则删除',
            //'尾箱拆除报警规则导出',
            //'尾箱拆除报警记录(父级权限)',
            //'尾箱拆除报警记录查看',
            //'尾箱拆除报警记录处理',
            //'尾箱拆除报警记录删除',
            //'尾箱拆除报警记录导出',
            //'定位标签配置(rssi阀值)(父级权限)',
            //'定位基站管理(父级权限)',
            //'定位基站管理查看',
            //'定位基站管理创建/修改',
            //'定位基站管理删除',
            //'定位基站管理导出',
            //'区域管理(父级权限)',
            //'区域管理查看',
            //'区域管理创建/修改',
            //'区域管理删除',
            //'区域管理导出',
            //'定位区域判断规则(父级权限)',
            //'定位区域判断规则查看',
            //'定位区域判断规则创建/修改',
            //'定位区域判断规则删除',
            //'定位区域判断规则导出',
        ];
    }


    /**
     * 插入数据
     *
     * @param      $importRecord
     * @param      $newRow
     *
     * @return mixed
     */
    public function dataHandler($importRecord, $newRow)
    {
        $subjectId = Subject::query()->where('name', $newRow['组织机构'])->first()->id ?? null;

        //if ( ! $subjectId) {
        //    $subjectId = $importRecord->subject_id;
        //}
        $roleArr = [];
        //判断角色是否存在
        if (isset($newRow['角色名(可空)'])) {
            $adminRole = Role::query()
                ->where('name', $newRow['角色名(可空)'])
                ->first();

            if ( ! $adminRole) {
                throw new ResourceException('角色名错误,不存在的角色:' . $newRow['角色名(可空)']);
            }

            array_push($roleArr, $adminRole->id);
        }

        $this->createAdminUserRole($subjectId,
            [
                'username' => $newRow['用户名(登录账号)'],
                'name'     => $newRow['名称'],
                'mobile'   => $newRow['手机号(可空)'],
                'password' => $newRow['密码(不写就和登录名一样)'],
            ],
            $roleArr);
    }


    /**
     * 导入验证规则
     *
     * @link https://docs.laravel-excel.com/3.1/imports/validation.html
     *
     * @param $importRecord
     *
     * @return array
     */
    public function rule($importRecord)
    {
        return [
            '用户名(登录账号)' => [
                'required',
                'unique:admin_users,username',
            ],
            '组织机构'         => [
                'nullable',
                Rule::exists('subjects', 'name'),
            ],
        ];
    }


    /**
     * 导入之前触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    public function beforeSheet($importRecord)
    {

    }


    /**
     * 导入之后触发
     *
     * @param $importRecord
     *
     * @return mixed
     */
    public function afterSheet($importRecord)
    {


    }


    public function filterPermission($permission)
    {
        //筛选出1的权限组
        $permissionArr = array_filter($permission, function ($k) {
            return $k == 1;
        }, ARRAY_FILTER_USE_BOTH);
        //筛选出带父级权限的权限组
        $ParentPermissionArr = array_filter($permissionArr, function ($k, $v) {
            return strpos($v, '父级权限');
        }, ARRAY_FILTER_USE_BOTH);
        //筛选出不带父级权限的权限组
        $noParentPermissionArr = array_filter($permissionArr, function ($k, $v) {
            return ! strpos($v, '父级权限');
        }, ARRAY_FILTER_USE_BOTH);
        //截取掉父级权限
        $ParentPermissionArr = array_map(function ($key) {
            return mb_substr($key, 0, -6);
        }, array_keys($ParentPermissionArr));
        $noParentPermissionKeyArr = array_keys($noParentPermissionArr);

        foreach ($noParentPermissionKeyArr as $k => $v) {
            foreach ($ParentPermissionArr as $vv) {
                if (strpos($v, $vv) !== false) {
                    unset($noParentPermissionKeyArr[$k]);
                }
            }
        }

        return array_merge($noParentPermissionKeyArr, $ParentPermissionArr);
    }
}
