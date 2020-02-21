<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
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
class AuthController extends Controller
{

    use AuthValidateTrait, OpenidCheckTrait, ValidatesRequests;

    /**
     * @var AdminUserUsecase
     */
    private $adminUserUsecase;


    /**
     * AuthController constructor.
     *
     * @param AdminUserUsecase $adminUserUsecase
     */
    public function __construct(AdminUserUsecase $adminUserUsecase)
    {
        $this->adminUserUsecase = $adminUserUsecase;
    }


    /**
     * 登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|User|null
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postLogin(Request $request)
    {
        switch ($request->header("REQUEST-TYPE")) {
            case 'WECHAT':
                //校验identifier(实际就是加密过得openid),确保只使用了一次
//                $request = $this->checkOpenid($request, 'identifier');

                return $this->loginByWechat($request);
                break;
            default:
                if ($request->username && $request->password) {
                    return $this->loginByUsername($request);
                }

                throw new ResourceException("不支持的登录方式:" . $request->header("REQUEST-TYPE"));
                break;

        }
    }


    /**
     * 微信端授权登录管理端
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Validation\ValidationException
     */
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

        $adminUser = $this->adminUserUsecase->getUserByOpenid($openid, $subject->id);

        if ( ! $adminUser) {
            throw new ResourceException("当前微信未绑定管理账号,请前往管理后台绑定");
        }

        //检查账号是否被禁用
        if ($adminUser->status == 'forbidden') {
            throw new PermissionDeniedException("当前账号已被禁用");
        }

        return $this->beforeReturnUser($adminUser);
    }


    /**
     * 管理端账号app登录
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function loginByUsername(Request $request)
    {

        $subject = SubjectUtils::getSubject();

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $adminUser = $this->adminUserUsecase->getUserByUsernameAndPassword($request->username,
            $request->password, $subject->id);

        if ( ! $adminUser) {
            throw new ResourceException('账号密码错误或账号不存在');
        }

        return $this->beforeReturnUser($adminUser);
    }


    /**
     * 返回管理端用户及检查
     *
     * @param $adminUser
     *
     * @return mixed
     */
    private function beforeReturnUser($adminUser)
    {
        //检查账号是否被禁用
        if ($adminUser->status == 'forbidden') {
            throw new PermissionDeniedException("当前账号已被禁用");
        }

        $adminUserUsecase = app(AdminUserUsecase::class);

        return $adminUserUsecase->getReturnUserInfo($adminUser, true);
    }


    ///**
    // * Get the guard to be used during authentication.
    // *
    // * @return \Illuminate\Contracts\Auth\PasswordBroker
    // */
    //protected function guard()
    //{
    //    $guard = 'admin_api';
    //
    //    return Auth::guard($guard);
    //}

}
