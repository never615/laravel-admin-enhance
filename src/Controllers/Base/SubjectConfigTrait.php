<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Form;
use Encore\Admin\Form\EmbeddedForm;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SubjectConfigConstants;
use Mallto\Tool\SubjectConfigConstants as ToolSubjectConfigConstants;
use Mallto\Tool\Data\Tag;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/5
 * Time: 下午12:13
 */
trait SubjectConfigTrait
{

    /**
     * 主体基本配置(owner,项目拥有者可以编辑,如mallto)
     *
     * 包含一些系统的基本配置
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function systemConfigBasic(Form $form)
    {
        //父级主体和已购模块只能父级设置,自己可以看,不能改
        $current = Subject::find($this->currentId);
        $parent = null;
        if ($current) {
            $parent = Subject::find($current->parent_id);
        }

        $form->select('parent_id', '父级主体')
            ->options(function () use ($parent) {
                if ($this->id == 1) {
                    $arr = Subject::query()->orderBy('id')->pluck('name', 'id');
                    array_add($arr, 0, '项目开发商');
                } else {
                    //返回自己有权限查看的和自己已经配置的
                    $arr = Subject::dynamicData()->orderBy('id')->pluck('name', 'id');
                    if ($parent) {
                        array_add($arr, $parent->id, $parent->name);
                    }
                }

                return $arr;
            })->rules('required');

        if (\Mallto\Admin\AdminUtils::isOwner()) {
            if ($this->currentId) {
                $form->displayE('sms_count', '消费短信数');
            }
            $form->text('uuid', '主体唯一标识');
            $form->text('wechat_uuid', '微信授权标识');
            $form->switch('base', '总部');

            $form->text('third_part_mall_id', '第三方项目标识');
        }
    }


    /**
     * 已购模块配置(owner,项目拥有者可以编辑,如mallto)
     *
     * 包含权限配置
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function purchasedModuleConfig(Form $form)
    {
        $permissions = Permission::where('common', false)
            ->orderby('order')
            ->get();

        $form->checkbox('permissions', '已购模块')
            ->options(Permission::selectOptions($permissions->toArray(),
                false, false))
            ->stacked();
    }


    /**
     * 系统配置中的json格式保存的配置项
     *
     * 项目拥有者配置:extra_config
     *
     *
     * //todo 优化配置逻辑,如果其他库有自定义的参数,且没有调用覆盖这个配置就会读取不出来,
     * 因为这个subjectcontroller中是写死的这几个配置
     *
     * @param $form
     */
    protected function systemConfigExtraConfigBasic(EmbeddedForm $form)
    {
        $form->text(SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID, '管理端微信服务uuid')
            ->help('用于微信开放平台授权,获取指定uuid对应的服务号下微信用户的openid,</br>
有的项目管理端单独使用一个公众号,所以需要配置单独的uuid');

        $form->text(ToolSubjectConfigConstants::OWNER_CONFIG_SMS_SIGN, '短信签名');

        $form->text(ToolSubjectConfigConstants::OWNER_CONFIG_SMS_TEMPLATE_CODE, '短信验证码模板号');

        $form->multipleSelect(SubjectConfigConstants::OWNER_CONFIG_TAG_TYPES, '可配置标签种类')
            ->options(Tag::TYPE);
    }

}
