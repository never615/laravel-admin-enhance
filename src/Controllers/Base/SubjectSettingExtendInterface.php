<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Form;

/**
 * User: never615 <never615.com>
 * Date: 2020/7/20
 * Time: 4:14 下午
 */
interface SubjectSettingExtendInterface
{

    /**
     * 主体拥有者可以编辑的配置,保存在subject_settings表中的subject_owner_configs列.json
     *
     * 该函数中的代码主体拥有者有权限可以看到
     *
     * 展示在一个tab中
     *
     * @param Form\EmbeddedForm $form
     * @param                   $currentId
     * @param                   $adminUser
     *
     * @return mixed
     */
    public function subjectOwnerConfig(Form\EmbeddedForm $form, $currentId, $adminUser);


    /**
     * 公开配置,可以通过接口请求到
     *
     * 该函数中的代码只有项目拥有者有权限可以看到
     *
     * 保存在subject_settings表中的public_configs列.json
     *
     * 展示在一个tab中
     *
     *
     * @param Form\EmbeddedForm $form
     * @param                   $currentId
     * @param                   $adminUser
     *
     * @return mixed
     */
    public function publicConfig(Form\EmbeddedForm $form, $currentId, $adminUser);


    /**
     * 私有配置,只有代码中可以使用
     *
     * 该函数中的代码只有项目拥有者有权限可以看到
     *
     * 保存在subject_settings表中的private_configs列.json
     *
     * 展示在一个tab中
     *
     *
     * @param Form\EmbeddedForm $form
     * @param                   $currentId
     * @param                   $adminUser
     *
     * @return mixed
     */
    public function privateConfig(Form\EmbeddedForm $form, $currentId, $adminUser);


    /**
     * 任意扩展配置
     *
     *
     * @param Form $form
     * @param      $currentId
     * @param      $adminUser
     *
     * @return mixed
     */
    public function extend(Form $form, $currentId, $adminUser);


    /**
     * @param Form $form
     *
     * @param      $adminUser
     *
     * @return mixed
     */
    public function formSaving(Form $form, $adminUser);


    /**
     * @param Form $form
     *
     * @return mixed
     */
    public function formSaved(Form $form);

}
