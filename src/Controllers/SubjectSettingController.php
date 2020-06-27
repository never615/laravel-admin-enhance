<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Form\EmbeddedForm;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Mall\Constants;
use Mallto\Mall\SubjectSettingConstants;

/**
 * Class SubjectSettingController
 *
 * @package Mallto\Admin\Controllers
 */
class SubjectSettingController extends AdminCommonController
{

    protected function title()
    {
        return '项目配置';
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return SubjectSetting::class;
    }


    protected function gridOption(Grid $grid)
    {
    }


    /**
     * 需要实现的form设置
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function defaultFormOption(Form $form)
    {
        $form->tab('银联闪付配置', function (Form $form) {
            $form->embeds('union_pay_setting', '', function (EmbeddedForm $form) {
                $form->select(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_DRIVER, '网关')
                    ->rules('required')
                    ->options(Constants::UNION_PAY_DRIVER);

                $form->text(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_MER_ID, '商户号')
                    ->rules('required');

                $form->filePrivate(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_PRIVATE_CERT_PATH,
                    '商户私钥证书')
                    ->rules('required')
                    ->options([
                        'allowedPreviewTypes'   => [],
                        'allowedFileExtensions' => [ 'pfx' ],
                    ])
                    ->move('union_pay');

                $form->text(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_CERT_DIR, '商户公钥证书目录')
                    ->rules('required');

                $form->filePrivate(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_ENC_CERT_PATH, '商户敏感加密证书')
                    ->rules('required')
                    ->options([
                        'allowedPreviewTypes'   => [],
                        'allowedFileExtensions' => [ 'cer' ],
                    ])
                    ->move('union_pay');
                $form->filePrivate(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_MIDDLE_CERT_PATH,
                    '商户中级证书')
                    ->rules('required')
                    ->options([
                        'allowedPreviewTypes'   => [],
                        'allowedFileExtensions' => [ 'cer' ],
                    ])
                    ->move('union_pay');
                $form->filePrivate(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_ROOT_CERT_PATH, '商户根证书')
                    ->rules('required')
                    ->options([
                        'allowedPreviewTypes'   => [],
                        'allowedFileExtensions' => [ 'cer' ],
                    ])
                    ->move('union_pay');

                $form->text(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_CERT_PASSWORD, '商户私钥密码')
                    ->rules('required');
                $form->url(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_NOTIFY_URL, '支付后接收回调地址')
                    ->rules('required');
                $form->url(SubjectSettingConstants::OWNER_SETTING_UNION_PAY_RETURN_URL, '支付后返回页面地址')
                    ->rules('required');
            });

            $this->formSubject($form);
            if ($this->currentId) {
                $this->formAdminUser($form);
                $form->displayE('created_at', trans('admin.created_at'));
                $form->displayE('updated_at', trans('admin.updated_at'));
            }
        });

        $form->saving(function ($form) {
            $this->autoSubjectSaving($form);
            $this->autoAdminUserSaving($form);

            if ( ! $this->currentId) {
                $subjectSettingExists = SubjectSetting::query()
                    ->where('subject_id', $form->subject_id)
                    ->exists();

                if ($subjectSettingExists) {
                    throw new SubjectConfigException('该主体已有配置，请勿新增');
                }
            }
        });

    }


    /**
     * 需要实现的form设置
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function formOption(Form $form)
    {

    }
}
