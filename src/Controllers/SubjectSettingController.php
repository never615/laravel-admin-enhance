<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Admin\Facades\AdminE;
use Mallto\Tool\Domain\App\ClearCacheUsecase;
use Mallto\Tool\Exception\ResourceException;
use Mallto\Tool\Utils\CacheUtils;

/**
 * Class SubjectSettingController.
 */
class SubjectSettingController extends AdminCommonController
{

    /**
     * 其他库添加的扩展配置
     *
     * @var array
     */
    public $expandSettingHandlers = [];


    protected function title()
    {
        return '项目配置';
    }


    /**
     * 获取这个模块的Model.
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
     * 需要实现的form设置.
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
        $adminUser = AdminUtils::getCurrentAdminUser();

        //初始化其他库添加的subject配置对象
        $subjectSettingExpands = AdminE::getSubjectSettingClass();

        foreach ($subjectSettingExpands as $expandSettingHandler) {
            $expandSettingHandler = app($expandSettingHandler);
            $this->expandSettingHandlers[] = $expandSettingHandler;
        }

        if (AdminUtils::isOwner()) {
            $form->tab('基本配置', function (Form $form) {
                $form->multipleSelect('front_column', '前端可以请求的列')
                    ->options(array_combine(Schema::getColumnListing('subject_settings'),
                        Schema::getColumnListing('subject_settings')))
                    ->help('配置在这里key或者public配置中的字段或者动态配置中的前端、公共配置前端才有权限请求');

                $form->multipleSelect('file_type_column', '文件类型的列')
                    ->options(array_combine(Schema::getColumnListing('subject_settings'),
                        Schema::getColumnListing('subject_settings')))
                    //->help('配置在这里的列前端请求的时候会自动加文件前缀,或者字段名包含image');
                    ->help('配置在这里的列前端请求的时候会自动加文件前缀');

                $this->formSubject($form);
                $this->formAdminUser($form);
                $form->displayE('created_at', trans('admin.created_at'));
                $form->displayE('updated_at', trans('admin.updated_at'));
            });
        }

        //主体拥有者可以自己配置的
        $form->tab('配置', function (Form $form) use ($adminUser) {
            $form->embeds('subject_owner_configs', '', function (Form\EmbeddedForm $form) use ($adminUser) {
                //动态属性列扩展,开放给主体拥有者可以编辑的
                foreach ($this->expandSettingHandlers as $subjectSettingExpand) {
                    $subjectSettingExpand->subjectOwnerConfig($form, $this->currentId, $adminUser);
                }
            });
        });

        if (AdminUtils::isOwner()) {
            $form->tab('public配置', function (Form $form) use ($adminUser) {
                $form->embeds('public_configs', '', function (Form\EmbeddedForm $form) use ($adminUser) {
                    //动态属性列扩展，前段可以通过接口请求
                    foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                        $expandSettingHandler->publicConfig($form, $this->currentId, $adminUser);
                    }
                });
            });

            $form->tab('private配置', function (Form $form) use ($adminUser) {
                $form->embeds('private_configs', '', function (Form\EmbeddedForm $form) use ($adminUser) {
                    //动态属性列扩展
                    foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                        $expandSettingHandler->privateConfig($form, $this->currentId, $adminUser);
                    }
                });
            });

            $form->tab('动态配置', function ($form) {
                $form->hasMany('subjectconfigs', '', function (NestedForm $form) {
                    $form->select('type')
                        ->options(SubjectConfig::TYPE);
                    $form->text('key');
                    $form->text('value');
                    $form->text('remark');
                });
            });
        }

        //这个模块的是否只有项目拥有者看到由具体添加者控制
        foreach ($this->expandSettingHandlers as $expandSettingHandler) {
            $expandSettingHandler->extend($form, $this->currentId, $adminUser);
        }

        $form->saving(function (Form $form) use ($adminUser) {

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

            foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                $expandSettingHandler->formSaving($form, $adminUser);
            }
        });

        $form->saved(function ($form) use ($adminUser) {
            foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                $expandSettingHandler->formSaved($form, $adminUser);
            }

            //重复key检查
            $this->repeatCheck($form);

            $this->clearCache($form);
        });
    }


    /**
     * 重复key检查
     *
     * @param Form $form
     */
    private function repeatCheck(Form $form)
    {
        $publicConfigsKeys = array_keys($form->model()->public_configs ?? []);
        $privateConfigsKeys = array_keys($form->model()->private_configs ?? []);
        $subjectOwnerConfigsKeys = array_keys($form->model()->subject_owner_configs ?? []);

        $columnKeys = array_keys(array_except($form->model()->toArray(), [
            'public_configs',
            'private_configs',
            'subject_owner_configs',
        ]));

        $keys = array_merge($publicConfigsKeys, $privateConfigsKeys, $subjectOwnerConfigsKeys, $columnKeys);

        //\Log::debug($keys);
        // 获取去掉重复数据的数组
        $uniqueArr = array_unique($keys);
        // 获取重复数据的数组
        $repeatArr = array_diff_assoc($keys, $uniqueArr);

        if (count($repeatArr) > 0) {
            throw new ResourceException('有重复key,需手动还原数据库重复数据,请检查key:' . json_encode(array_values($repeatArr)));
        }
    }


    /**
     * 清理缓存
     *
     * @param $form
     */
    private function clearCache($form)
    {
        $prefix = SubjectSetting::getCacheKey($form->subject_id);

        $clearCacheUsecase = app(ClearCacheUsecase::class);
        $clearCacheUsecase->clearCache(true, $prefix);
    }


    /**
     * 需要实现的form设置.
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @return mixed
     */
    protected
    function formOption(
        Form $form
    ) {
    }
}
