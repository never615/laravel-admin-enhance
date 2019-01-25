<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/1/25
 * Time: 4:19 PM
 */
trait  AdminBaseHelp
{
    protected function formMultipleImage($form, $columnName, $tableName = "easy")
    {
        $form->multipleImage($columnName)
            ->help("图片最大不能超过2M")
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->removable()
            ->uniqueName()
            ->move("$tableName/$columnName/".$this->currentId);
    }


    protected function formImage($form, $columnName, $tableName = "easy")
    {
        $form->image($columnName)
            ->help("图片最大不能超过2M")
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->uniqueName()
            ->removable()
            ->move("$tableName/$columnName/".$this->currentId);
    }

}