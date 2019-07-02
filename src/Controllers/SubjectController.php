<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\EmbeddedForm;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Controllers\Base\SubjectSaveTrait;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\SubjectConfigConstants;
use Mallto\Tool\Data\Tag;


class SubjectController extends AdminCommonController
{
    use SubjectSaveTrait;

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "主体";
    }

    /**
     * 获取这个模块的Model
     *o
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Subject::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->name()->sortable();
        $grid->parent_id("归属")->display(function ($parent_id) {
            $subject = Subject::find($parent_id);
            if ($subject) {
                return $subject->name;
            } else {
                if ($parent_id == 0) {
                    return "项目开发商";
                } else {
                    return "";
                }

            }
        })->sortable();

        if (\Mallto\Admin\AdminUtils::isOwner()) {
            $grid->uuid()->editable();
        }


        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike("name");
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if (Admin::user()->subject->id == $actions->row->id) {
                $actions->disableDelete();
            }
            $actions->disableView();
        });

    }

    /**
     * 如果form中使用到了tab,需要复写此方法
     *
     * @param Form $form
     */
    protected function defaultFormOption(Form $form)
    {
        $form = $form->tab("基本信息", function ($form) {
            $form->text("name")->rules('required');
            $this->formSubject($form);
            $this->formAdminUser($form);
        });


        $form = $form->tab("配置项", function ($form) {
            $form->embeds("open_extra_config", "", function ($form) {
                $this->openConfigBasic($form);
            });
        });

        $form = $form->tab("系统配置(owner)", function ($form) {
            $this->systemConfigBasic($form);

            if (\Mallto\Admin\AdminUtils::isOwner()) {
                $form->embeds("extra_config", "其他配置", function (EmbeddedForm $form) {
                    $this->systemConfigExtraConfigBasic($form);
                });
            }

        })->tab("系统参数(owner)", function ($form) {
            if (\Mallto\Admin\AdminUtils::isOwner()) {
                $form->hasMany("subjectconfigs", "", function (Form\NestedForm $form) {
                    $form->select("type")
                        ->options(SubjectConfig::TYPE);
                    $form->text("key");
                    $form->text("value");
                    $form->text("remark");
                });
            }
        });


        $form->saving(function ($form) {
            $adminUser = Admin::user();

            $this->saving($adminUser, $form);
        });

        $form->saved(function ($form) {
            $this->createOrUpdateAdminRole($form);
        });
    }


    protected function openConfigBasic(EmbeddedForm $form)
    {
        $form->multipleSelect(SubjectConfigConstants::SUBJECT_OWNER_CONFIG_QUICK_ACCESS_MENU, "快捷访问菜单")
            ->help("顶部菜单栏上的快捷访问菜单,在此配置后,拥有对应菜单权限的账号即可在快捷访问中看到对应菜单")
            ->options(Menu::selectOptions());
    }

    protected function systemConfigBasic($form)
    {
        //父级主体和已购模块只能父级设置,自己可以看,不能改
        $current = Subject::find($this->currentId);
        $parent = null;
        if ($current) {
            $parent = Subject::find($current->parent_id);
        }

        $form->select("parent_id", "父级主体")->options(function () use ($parent) {
            if ($this->id == 1) {
                $arr = Subject::pluck('name', 'id');
                array_add($arr, 0, "项目开发商");
            } else {
                //返回自己有权限查看的和自己已经配置的
                $arr = Subject::dynamicData()->pluck("name", "id");
                if ($parent) {
                    array_add($arr, $parent->id, $parent->name);
                }
            }


            return $arr;
        })->rules("required");


        if (\Mallto\Admin\AdminUtils::isOwner()) {
            $permissions = Permission::
//            where("parent_id", 0)
                where("common", false)
                ->orderby("order")
                ->get();

            $form->checkbox('permissions', "已购模块")
                ->options(Permission::selectOptions($permissions->toArray(),
                    false, false))
            ->stacked();

            if ($this->currentId) {
                $form->displayE('sms_count', "消费短信数");
            }
            $form->text("uuid", "主体唯一标识");
            $form->switch("base", "总部");
        }
    }

    /**
     * 系统配置中的json格式保存的配置项
     *
     * @param $form
     */
    protected function systemConfigExtraConfigBasic(EmbeddedForm $form)
    {
        $form->text(SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID, "管理端微信服务uuid")
            ->help("用于微信开放平台授权,获取指定uuid对应的服务号下微信用户的openid");

        $form->text(SubjectConfigConstants::OWNER_CONFIG_SMS_SIGN, "短信签名");

        $form->text(SubjectConfigConstants::OWNER_CONFIG_SMS_TEMPLATE_CODE, "短信验证码模板号");

        $form->multipleSelect(SubjectConfigConstants::OWNER_CONFIG_TAG_TYPES, "可配置标签种类")
            ->options(Tag::TYPE);
    }

    protected function formOption(Form $form)
    {
    }
}
