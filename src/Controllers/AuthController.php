<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Mallto\Admin\Domain\User\AdminUserUsecase;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Data\User;
use Mallto\User\Domain\Traits\AuthValidateTrait;
use Mallto\User\Domain\Traits\OpenidCheckTrait;

/**
 * 管理端账户登录
 *
 *
 * 管理端和微信绑定支持两种方式:
 *  1. 管理端->账户管理->直接扫码绑定需要绑定的微信
 *  2. 管理端->账户管理->绑定已注册的会员的手机号.(实际上会通过该手机号查询对应的openid,从而和微信进行关联)
 *
 * Class AuthController
 *
 * @package Mallto\Admin\Controllers
 */
class AuthController extends \Encore\Admin\Controllers\AuthController
{

    use AuthValidateTrait, OpenidCheckTrait, ValidatesRequests;


    /**
     * 登录
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|User|null
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function postLogin(Request $request)
    {

        switch ($request->header("REQUEST-TYPE")) {
            case "WECHAT":
                //校验identifier(实际就是加密过得openid),确保只使用了一次
//                $request = $this->checkOpenid($request, 'identifier');

                return $this->loginByWechat($request);
                break;
            default:
                return parent::postLogin($request);
                break;

        }
    }

    public function loginByWechat(Request $request)
    {
        //请求字段验证
        //验证规则
        $rules = [];
        $rules = array_merge($rules, [
            "identifier" => "required",
        ]);
        $this->validate($request, $rules);

        $this->isWechatRequest($request);

        $subject = SubjectUtils::getSubject();

        $openid = $this->decryptOpenid($request->identifier);

        $adminUserUsecase = app(AdminUserUsecase::class);
        $adminUser = $adminUserUsecase->getUserByOpenid($openid, $subject->id);

        if (!$adminUser) {
            throw new ResourceException("当前微信未绑定管理账号,请前往管理后台绑定");
        }

        //检查账号是否被禁用
        if ($adminUser->status == "forbidden") {
            throw new PermissionDeniedException("当前账号已被禁用");
        }

        $adminUserUsecase = app(AdminUserUsecase::class);

        return $adminUserUsecase->getReturnUserInfo($adminUser, true);
    }

}
