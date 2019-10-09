<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\ImportSetting;


/**
 * 生成权限的seeder基础方法
 *
 * Create by PhpStorm.
 * User: never615
 * Date: 24/04/2017
 * Time: 4:51 PM
 */
trait ImportSettingSeederMaker
{

    /**
     * @param      $templateIntroduce (模板说明)
     * @param      $templateTag  (模板标识)
     * @param      $templateDealClass (模板处理类)
     * @return mixed
     */
    public function UpdateOrCreate($templateTag,$templateDealClass,$fileUrl = '', $templateIntroduce = ''){
        return ImportSetting::updateOrCreate(
            [
                'module_slug'  =>       $templateTag
            ],[
            'name'         =>       $templateIntroduce,
            'module_handler'       =>       $templateDealClass,
            'template_url'         =>       $fileUrl,
            'template_with_annotation_url'  =>  $fileUrl,
        ]);
    }

}
