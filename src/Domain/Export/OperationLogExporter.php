<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Export;

use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\OperationLogDictionary;
use Mallto\Admin\Grid\Exporters\SimpleCsvExporter;

class OperationLogExporter extends SimpleCsvExporter
{

    /**
     * 默认移除的key
     *
     * @var array
     */
    protected $defaultForgetKeys = [];

    /**
     * 是否使用$this->remainKeys()返回的key的顺序作为csv header的顺序
     *
     * @var bool
     */
    protected $useRemainKeySort = true;


    /**
     * 数据加工
     *
     * 加工数据的时候要注意,每一个record输出时key的数量应该是相等的
     *
     * @param array $record
     *
     * @return array $record,需要返回处理后的$record
     */
    public function mapper($record)
    {
        $method = $record['method'];
        $path = $record['path'];
        $record['user_id'] = Administrator::query()->find($record['user_id'])->name ?? null;
        //判断增删改查
        if ($method === 'GET') {
            $method = '查看';
            if (strpos($path, 'create') !== false) {
                $method = '创建';
            }
            if (strpos($path, 'edit') !== false) {
                $method = '查看详情';
            }
        } elseif ($method === 'POST') {
            $method = '保存';
        } elseif ($method === 'PUT') {
            $method = '更新';
        } elseif ($method === 'DELETE') {
            $method = '删除';
        }

        $value = null;
        //新增处理
        if (strpos($path, 'create') !== false) {
            $value = str_replace('/create', '', $path);
        }

        //查看详情处理
        if (strpos($path, 'edit') !== false) {
            $str_value = str_replace('/edit', '', $path);
            $str_len_value = strrpos($str_value, '/');
            $value = substr($str_value, 0, $str_len_value);
        }

        //更新处理
        if ($method === 'PUT' || $method === 'DELETE') {
            $str_len_value = strrpos($path, '/');
            $value = substr($path, 0, $str_len_value);
        }

        $operationLogDictionary = OperationLogDictionary::query()
            ->where('path', '~*', $path)
            ->first();

        $path = $operationLogDictionary->name ?? $value;

        return [
            '操作人'   => $record['user_id'],
            '行为'    => $method,
            '请求名称'  => $path,
            'ip'    => $record['ip'],
            '创建时间'  => $record['created_at'],
        ];
    }


    /**
     * 返回要保留的key
     *
     * 设置此项,则forgetKeys()设置无效
     *
     * @return array
     */
    public function remainKeys()
    {
        return [
            "操作人",
            "行为",
            "请求名称",
            "ip",
            "创建时间",
        ];
    }


    /**
     * 返回要移除的key
     *
     * 参数可以传入关联数据的**模型名**来忽略该模型下的全部数据,
     * 如导出user数据的时候,传入member会忽略user关联的member对象下的所有字段.
     *
     * 也可以使用member.name移除关联模型的指定字段
     *
     *
     * 默认移除了一些字段参见  @return array
     *
     * @var $this ->defaultForgetKeys
     *
     */
    public function forgetKeys()
    {
        // TODO: Implement forgetKeys() method.
    }

}
