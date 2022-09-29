<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\ImportSetting;

/**
 * 生成导入模板的seeder基础方法
 *
 * Create by PhpStorm.
 * User: never615
 * Date: 24/04/2017
 * Time: 4:51 PM
 */
trait ImportSettingSeederMaker
{

    /**
     * @param      $templateTag       (模板标识)
     * @param      $templateIntroduce (模板说明) 如:Mallto\Mall\Domain\Import\MemberImport
     * @param      $templateDealClass (模板处理类)
     *
     * @return mixed
     */
    public function UpdateOrCreate($templateTag, $templateDealClass, $fileUrl = '', $templateIntroduce = '')
    {
        return ImportSetting::updateOrCreate(
            [
                'module_slug' => $templateTag,
            ], [
            'name'                         => $templateIntroduce,
            'import_handler'               => $templateDealClass,
            //'template_url'                 => $fileUrl,
            'template_with_annotation_url' => $fileUrl,
        ]);
    }


    public function getFileUrlPrefix()
    {
        return config('app.url') . '/vendor/file/';
    }

}
