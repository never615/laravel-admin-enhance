<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;

/**
 * 直传文件到七牛,然后form提交文件的url到服务器,进行保存
 */
class QiniuFile extends Field\File
{

    protected $view = 'adminE::form.qiniu_file';


    /**
     * Prepare for saving.
     *
     * 前端只提交了文件路径,只需要保存文件
     *
     * @param $filePath
     *
     * @return mixed|string
     */
    public function prepare($filePath)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }

        if ($filePath && is_string($filePath)) {
            $paths = explode($filePath, "/");
            $this->name = array_last($paths);
        } else {
            $this->name = $filePath;
        }

        $this->destroy();

        return $filePath;
    }

}
