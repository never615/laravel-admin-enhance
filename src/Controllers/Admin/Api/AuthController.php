<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
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

    use AuthValidateTrait, OpenidCheckTrait, ValidatesRequests, ThrottlesLogins;

    /**
     * 最多错误次数
     */
    protected $maxAttempts = 5;

    /**
     * 账号锁定分钟
     */
    protected $decayMinutes = 5;

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
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (strlen($request->password) >= 20) {
            //验证验证码对不对
            $capthca = $request->input('captcha');
            $captchaKey = $request->input('captcha_key');
            if ( ! captcha_api_check($capthca, $captchaKey)) {
                throw new ResourceException('验证码不匹配');
            }

            $key = "1E390CMD585LLS4S"; //与JS端的KEY一致
            $iv = "1104432290129056"; //这个也是要与JS中的IV一致

            $request->password = openssl_decrypt(base64_decode($request->password), "AES-128-CBC", $key,
                OPENSSL_RAW_DATA, $iv);
        }

        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            throw new ResourceException(Lang::get('auth.throttle', [ 'seconds' => $seconds ]));
        }

        $permissons = $request->permissions;

        $permissons = $permissons ? explode(',', $permissons) : [ 'admin_api_manager' ];

        $adminUser = $this->adminUserUsecase->getUserByUsernameAndPassword($request->username,
            $request->password);

        if ( ! $adminUser) {
            $this->incrementLoginAttempts($request);
            //错误次数
            $num = $this->limiter()->attempts($this->throttleKey($request));
            //剩余次数
            $s_num = $this->maxAttempts - $num;

            $errorLog = '账号密码错误或账号不存在,剩余登录次数:' . $s_num;
            if ($s_num == 0) {
                $errorLog = "太多次尝试登入,请在 $seconds 秒再次尝试.";
            }
            throw new ResourceException($errorLog);
        }

        $this->clearLoginAttempts($request);

        return $this->beforeReturnUser($adminUser, $permissons);
    }


    /**
     * 返回管理端用户及检查
     *
     * @param $adminUser
     *
     * @return mixed
     */
    private function beforeReturnUser($adminUser, $permission = [ 'admin_api_manager' ])
    {
        //检查账号是否被禁用
        if ($adminUser->status == 'forbidden') {
            throw new PermissionDeniedException("当前账号已被禁用");
        }

        $adminUserUsecase = app(AdminUserUsecase::class);

        return $adminUserUsecase->getReturnUserInfo($adminUser, true, $permission);
    }


    protected function username()
    {
        return 'username';
    }


    //生成图片验证码
    public function captcha()
    {
        return [
            'code'   => 200,
            'status' => 'ok',
            'msg'    => '成功',
            'url'    => app('captcha')->create('default', true),
        ];
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
