<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\EmbeddedForm;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\CacheUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Controllers\Base\SubjectConfigTrait;
use Mallto\Admin\Controllers\Base\SubjectSaveTrait;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Facades\AdminE;
use Mallto\Admin\Listeners\Events\SubjectSaved;
use Mallto\Admin\SubjectConfigConstants;
use Mallto\Admin\Domain\Import\SubjectImport;
use Mallto\Tool\Data\Tag;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Admin\Grid\Tools\ImportButton;

class SubjectController extends AdminCommonController
{

    use SubjectSaveTrait, SubjectConfigTrait;

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return '主体';
    }


    /**
     * 获取这个模块的Model
     *o
     *
     * @return mixed
     */
    protected function getModel()
    {
        return config('other.subject', Subject::class);
    }


    protected function gridOption(Grid $grid)
    {
        $grid->name()->sortable();
        $grid->parent_id('归属')->display(function ($parent_id) {
            $subject = Subject::find($parent_id);
            if ($subject) {
                return $subject->name;
            } else {
                if ($parent_id == 0) {
                    return '项目开发商';
                } else {
                    return '';
                }

            }
        })->sortable();

        if (\Mallto\Admin\AdminUtils::isOwner()) {
            $grid->uuid()->editable();
        }

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('name');

            $filter->equal('parent_id', '父级')
                ->select(Subject::dynamicData()->pluck('name', 'id'));
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if (Admin::user()->subject->id == $actions->row->id) {
                $actions->disableDelete();
            }
            $actions->disableView();
        });
        $grid->tools(function (Tools $tools) {
            $tools->append(new ImportButton(SubjectImport::class));
        });
    }


    protected $subjectConfigExpandObjs = [];


    /**
     * 如果form中使用到了tab,需要复写此方法
     *
     * @param Form $form
     */
    protected function defaultFormOption(Form $form)
    {
        //初始化其他库添加的subject配置
        //$subjectConfigExpands = config('other.subject_config_expands', []);
        $subjectConfigExpands = AdminE::getSubjectConfigClass();

        foreach ($subjectConfigExpands as $subjectConfigExpand) {
            $this->subjectConfigExpandObjs[] = app($subjectConfigExpand);
        }

        $form = $form->tab('基本信息', function ($form) {

            $form->displayE('id');

            $form->text('name')->rules('required');

            $this->basicInfoExtend($form);

            $this->formSubject($form);
            $this->formAdminUser($form);

            $form->displayE('created_at', trans('admin.created_at'));
            $form->displayE('updated_at', trans('admin.updated_at'));
        });

        $form->tab('配置项', function ($form) {
            $form->embeds('open_extra_config', '', function (Form\EmbeddedForm $form) {
                //动态属性列扩展,开放给主体拥有者可以编辑的
                $this->subjectOwnerExtraConfigByJson($form);
            });
        });

        $this->subjectOwnerExtend($form);

        if (
            \Mallto\Admin\AdminUtils::isOwner()
            || \Mallto\Admin\AdminUtils::isBase()
            || config('other.subject_parent_config')
        ) {
            $form->tab('主体基本配置(owner)', function ($form) {
                //主体基本配置(owner) uuid/权限
                $this->systemConfigBasic($form);
            });
        }

        if (\Mallto\Admin\AdminUtils::isOwner()
            || config('other.subject_parent_config')) {
            $form->tab('已购模块配置(owner)', function ($form) {
                //主体基本配置(owner) uuid/权限
                $this->purchasedModuleConfig($form);
            });
        }

        if (AdminUtils::isOwner()) {
            $form->tab('主体配置(owner)', function ($form) {
                //extra_config保存,如数据库直接增加字段保存的停车系统和extra_config保存的订单系统等
                $this->projectOwnerConfig($form);
            });

            //主体动态参数(owner)
            $this->systemDynamicConfig($form);
        }

        $form->saving(function ($form) {
            $this->formSaving($form);
        });

        $form->saved(function ($form) {
            $this->formSaved($form);

        });
    }


    /**
     * 基本信息扩展
     *
     * @param $form
     */
    protected function basicInfoExtend($form)
    {
        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->basicInfoExtend($form);
        }

    }


    /**
     * 动态属性列扩展,开放给主体拥有者可以编辑的
     *
     * 一个json字段保存
     *
     * @param $form
     */
    protected function subjectOwnerExtraConfigByJson($form)
    {
        $form->multipleSelect(SubjectConfigConstants::SUBJECT_OWNER_CONFIG_QUICK_ACCESS_MENU, '快捷访问菜单')
            ->help('顶部菜单栏上的快捷访问菜单,在此配置后,拥有对应菜单权限的账号即可在快捷访问中看到对应菜单')
            ->options(Menu::selectOptions());

        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->subjectOwnerExtraConfigByJson($form);
        }
    }


    /**
     * 主体拥有者扩展tab
     * 可以新建tab
     *
     * @param $form
     */
    protected function subjectOwnerExtend(Form $form)
    {
        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->subjectOwnerExtend($form, $this->currentId);
        }
    }


    /**
     * 项目拥有者可以配置的
     */
    protected function projectOwnerConfig($form)
    {
        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->projectOwnerConfig($form);
        }
        //$form->textarea('extra_config');
        $form->embeds('extra_config', '其他配置', function (EmbeddedForm $form) {
            $form->text(SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID, '管理端微信服务uuid')
                ->help('用于微信开放平台授权,获取指定uuid对应的服务号下微信用户的openid,</br>
有的项目管理端单独使用一个公众号,所以需要配置单独的uuid');

            $form->multipleSelect(SubjectConfigConstants::OWNER_CONFIG_TAG_TYPES, '可配置标签种类')
                ->options(Tag::TYPE);

            foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
                $subjectConfigExpandObj->projectOwnerExtraConfigByJson($form);
            }
        });
    }


    /**
     * 主体动态配置,项目拥有者可以配置
     *
     * 一对多表保存
     *
     * @param $form
     * @param $adminUser
     */
    protected function systemDynamicConfig($form)
    {
        $form->tab('主体动态参数(owner)', function ($form) {
            $form->html('<h4>主要用来配置api接口地址和appKey和secret等</h4>');
            if (AdminUtils::isOwner()) {
                $form->hasMany('subjectconfigs', '', function (NestedForm $form) {
                    $form->select('type')
                        ->options(SubjectConfig::TYPE);
                    $form->text('key');
                    $form->text('value');
                    $form->text('remark');
                });
            }
        });
    }


    protected function formSaving($form)
    {
        $adminUser = Admin::user();

        $this->saving($adminUser, $form);

        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->formSaving($form, $adminUser);
        }
    }


    protected function formSaved($form)
    {
        $adminUser = Admin::user();

        //clear 顶部菜单的缓存
        Cache::forget('speedy_' . $adminUser->id);

        CacheUtils::forgetSubject($form->model()->id);

        event(new SubjectSaved($form->model()->id));

        foreach ($this->subjectConfigExpandObjs as $subjectConfigExpandObj) {
            $subjectConfigExpandObj->formSaved($form, $adminUser);
        }

        if ($adminUser && ! $adminUser->isOwner()) {
            if (
                ($form->third_part_mall_id && $form->third_part_mall_id != $form->model()->third_part_mall_id) ||
                ($form->uuid && $form->uuid != $form->model()->uuid)
            ) {
                throw new PermissionDeniedException('没有权限修改,请联系墨兔管理修改');
            }
        }
    }


    protected function formOption(Form $form)
    {
    }
}
