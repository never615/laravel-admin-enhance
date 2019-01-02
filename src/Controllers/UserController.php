<?php

namespace Mallto\Admin\Controllers;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SelectConstants;
use Mallto\Admin\SubjectUtils;
use Mallto\Mall\SubjectConfigConstants;
use Mallto\Tool\Exception\ResourceException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends AdminCommonController
{
    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "管理账户";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Administrator::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->username(trans('admin.username'));
        $grid->name(trans('admin.name'));
        $grid->roles(trans('admin.roles'))->pluck('name')->label();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->equal("adminable_type", "账号类型")->select(SelectConstants::ADMINABLE_TYPE);
            $filter->ilike("username", trans('admin.username'));
            $filter->ilike("name", trans('admin.name'));
        });


        $grid->actions(function (Grid\Displayers\Actions $actions) {
            //不能删除自己
            if (Admin::user()->id == $actions->row->id) {
                $actions->disableDelete();
            }
            $actions->disableView();
        });
    }


    protected function formOption(Form $form)
    {
        $form->text('username', trans('admin.username'))
            ->help("登录名")
            ->rules('required');

        $form->text('name', trans('admin.name'))->rules('required');

        $this->formWechatBind($form);
        $form->image('avatar', trans('admin.avatar'))->removable();
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))
            ->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });


        //绑定微信,一个链接,包含时间戳,要绑定的管理端账号的id,加密签名
//        $form->multipleSelect("manager_subject_ids", "数据查看范围")
//            ->help("不设置,则默认只能查看管理账号所属主体下的数据")
//            ->options(function () {
//                $user = Admin::user();
//                $subject = $user->subject;
//                $baseSubject = $subject->baseSubject();
//                $childrenSubjectIds = $baseSubject->getChildrenSubject();
//                $tempSubjects = Subject::whereIn("id", $childrenSubjectIds)
//                    ->get();
//
//                return Subject::selectOptions($tempSubjects->toArray(), false, false, $baseSubject->parent_id);
//            });


        $form->ignore(['password_confirmation', 'qrcode', 'unbind_wechat']);

        $form->select("adminable_type", "账号类型")
            ->options(SelectConstants::ADMINABLE_TYPE)
            ->rules("required");

        $form->selectE("adminable_id", "账号所属")
            ->rules("required")
            ->help("按空格搜索全部")
            ->options(function ($value) {
                if (!empty($value)) {
                    switch ($this->adminable_type) {
                        case 'subject':
                            $subject = Subject::find($value);
                            if ($subject) {
                                return $subject->pluck("name", "id");
                            }
                            break;
                    }
                }
            })
            ->ajaxLoad("adminable_type", data_source_url("ajax_load"));


        $form->multipleSelect('roles', trans('admin.roles'))
            ->options(Role::dynamicData()->get()->pluck('name', 'id'));

        $form->saving(function (Form $form) {
            //检查账户名称是否已经存在
            if ($form->username && ($form->username != $form->model()->username)) {

                $subjectId = $form->subject_id ?: $form->model()->subject_id;
                $adminUser = Administrator::where("subject_id", $subjectId)
                    ->where("username", $form->username)
                    ->first();

                if ($adminUser) {
                    throw new ResourceException("用户名".$form->username."已经存在");
                }
            }

            //暂时屏蔽manager_subject_ids逻辑
            //设置数据查看范围的时候,设置了父范围,就不能设置子范围,做检查
//            if ($form->manager_subject_ids &&
//                !$this->equlityManagerSubjectIds($form->manager_subject_ids,
//                    $form->model()->manager_subject_ids)
//            ) {
//                //检查提交来的数据,是否同时包含了父子级
//                $managerSubjectIds = $form->manager_subject_ids;
//
//                foreach ($managerSubjectIds as $managerSubjectId) {
//                    if ($managerSubjectId) {
//                        //获取它的父级们,看看提交的数组中有没有包含的
//                        $tempSubject = Subject::find($managerSubjectId);
//
//                        $tempParentSubjectIds = $tempSubject->getParentSubjectIds();
//
//                        foreach ($managerSubjectIds as $managerSubjectId) {
//                            if (in_array($managerSubjectId, $tempParentSubjectIds)) {
//                                //提交上来的数据,存在某个id的父级id,抛出错误
//                                throw new HttpException(422, "数据查看范围:设置了父级就不能同时设置子级");
//                            }
//                        }
//                    }
//                }
//
//            }


            //自己不能修改自己的角色
            if ($form->roles && !$this->equalRoleCheck($form->roles,
                    $form->model()->roles) && Admin::user()->id == $form->model()->id
            ) {
                throw new AccessDeniedHttpException("自己不能修改自己的角色");
            }

            if ($form->password && $form->model() && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }
        });
    }


    /**
     * form表单提交的和用户现有的角色是不是相等
     */
    protected function equalRoleCheck($formRoles, $roles)
    {
        $formRoles = array_filter($formRoles, function ($value) {
            return !empty($value) ? true : false;
        });


        return empty(array_diff($formRoles, $roles->pluck('id')->toArray())) ? true : false;
    }

    /**
     * form表单提交的和用户现有的角色是不是相等
     *
     * @param $formIds
     * @param $ids
     * @return bool
     */
    protected function equalManagerSubjectIds($formIds, $ids)
    {
        $formIds = array_filter($formIds, function ($value) {
            return !empty($value) ? true : false;
        });

        return $formIds == $ids;
    }


    /**
     * 生成绑定微信的url
     *
     * @param $adminUserId
     * @return string
     */
    protected function getBindWechatUrl($adminUserId)
    {
        $adminUser = Administrator::find($adminUserId);

        $subject = $adminUser->subject;
        $uuid = $subject->uuid;

        if (config("app.env") == "production" || config("app.env") == "staging") {
            $wechatOAuthUrl = "https://wechat.mall-to.com/wechat/oauth";
            $redirectUrl = config("app.url")."/admin/admin_bind_wechat";
        } else {
            $wechatOAuthUrl = "https://test-wechat.mall-to.com/wechat/oauth";
            $redirectUrl = config("app.url")."/admin/admin_bind_wechat";
        }

        $redirectUrl .= "?admin_user_id=".$adminUser->id;

        $queryDataStr = http_build_query([
            "uuid"         => SubjectUtils::getConfigByOwner(SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                $subject, $uuid),
            "redirect_url" => $redirectUrl,
        ]);

        $wechatOAuthUrl .= "?".$queryDataStr;

        return $wechatOAuthUrl;
    }

    /**
     * 绑定微信form
     *
     * @param $form
     */
    protected function formWechatBind($form)
    {
        if ($this->currentId) {
            $form->html("<h3>绑定微信</h3>");

            $currentAdminUser = Administrator::find($this->currentId);
            if ($currentAdminUser) {
                $qrcodeHelp = "";
                if ($currentAdminUser->openid) {
                    $qrcodeHelp = "已绑定微信,绑定用户微信昵称为:".$currentAdminUser->openid["nickname"].",扫码可重新绑定为其他用户";
                } else {
                    $qrcodeHelp = "未绑定微信,扫码可绑定(一个微信只能绑定一个账号,绑定新的账号后,旧的绑定关系会失效)";
                }
                $form->qrcode("qrcode", "扫码绑定微信")
                    ->qrcodeUrl($this->getBindWechatUrl($this->currentId))
                    ->help($qrcodeHelp);

                $form->buttonE("unbind_wechat", "解绑微信")
                    ->on("click", function () use ($currentAdminUser) {
                        $uuid = $currentAdminUser->subject->uuid;

                        return <<<EOT
        var loadIndex = layer.load(0, {shade: false}); //0代表加载的风格，支持0-2
                        
        $.ajax({
            type: 'GET',
            url: '/admin/admin_unbind_wechat',
            dataType: "json",
            data: {iddd: Math.random(),id:{$currentAdminUser->id}},
            headers: {
                'REQUEST-TYPE': 'WEB',
                'Accept': 'application/json',
                'UUID': '{$uuid}'
            },
            success: function (data) {
                layer.close(loadIndex);
                toastr.success("解绑成功");
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.close(loadIndex);
                errorHandler(XMLHttpRequest);
            }
        });                        
EOT;
                    });

            }

            $form->divider();
        }
    }

}
