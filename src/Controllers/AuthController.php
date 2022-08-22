<?php

namespace Mallto\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Mallto\Admin\Data\Administrator;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Domain\SmsUsecase;

class AuthController extends BaseAuthController
{

    use ThrottlesLogins;

    /**
     * 最多错误次数
     */
    protected $maxAttempts = 5;

    /**
     * 账号锁定分钟
     */
    protected $decayMinutes = 5;


    /**
     * 手机号登录方式
     *
     * @return string
     */
    public function mobile()
    {
        return 'mobile';
    }


    /**
     * 短信验证码登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function smsLogin(Request $request)
    {
        $remember = $request->get('remember', false);

        $this->loginSmsValidator($request->all())->validate();

        $adminUser = Administrator::query()->where('mobile', $request->mobile)->first();

        $sms = app(SmsUsecase::class);

        try {
            $sms->checkVerifyCode($request->mobile, $request->verify_number, 'admin_sms_login',
                $adminUser->subject_id);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'verify_number' => '验证码错误或已过期',
            ]);
        }

        if ($this->guard()->loginUsingId($adminUser->id, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->mobile() => $this->getFailedLoginMessage(),
            'login_page'    => 'sms',
        ]);
    }


    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        if (in_array($this->mobile(), $request->keys())) {
            return $this->smsLogin($request);
        }

        $this->loginValidator($request->all())->validate();

        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            return back()->withInput()->withErrors([
                $this->username() => [ Lang::get('auth.throttle', [ 'seconds' => $seconds ]) ],
            ]);
        }

        $credentials = $request->only([ $this->username(), 'password', 'captcha' ]);

        //验证预发布/正式环境
        if (in_array(config('app.env'), [ 'staging', 'production' ]) || ! config('app.debug')) {
            $validator = Validator::make($credentials, [
                'captcha' => 'required|captcha',
            ], [ 'captcha.captcha' => '验证码不匹配' ]);

            if ($validator->fails()) {
                return back()->withInput()->withErrors($validator);
            }
        }

        unset($credentials['captcha']);

        if (strlen($credentials['password']) >= 20) {
            $key = "1E390CMD585LLS4S"; //与JS端的KEY一致
            $iv = "1104432290129056"; //这个也是要与JS中的IV一致
            $credentials['password'] = openssl_decrypt(base64_decode($credentials['password']), "AES-128-CBC",
                $key, OPENSSL_RAW_DATA, $iv);
        }

        $remember = $request->get('remember', false);
        if ($this->guard()->attempt($credentials, $remember)) {
            $res = $this->sendLoginResponse($request);

            $this->clearLoginAttempts($request);

            return $res;
        }

        $this->incrementLoginAttempts($request);
        //错误次数
        $num = $this->limiter()->attempts($this->throttleKey($request));
        //剩余次数
        $s_num = $this->maxAttempts - $num;

        $errorLog = $this->getFailedLoginMessage() . '，' . Lang::get('auth.throttle_snum',
                [ 's_num' => $s_num ]);

        if ($s_num == 0) {
            $errorLog = "太多次尝试登入, 请在 $seconds 秒再次尝试.";
        }

        return back()->withInput()->withErrors([
            $this->username() => $errorLog,
            'login_page'      => 'password',
        ]);
    }


    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginSmsValidator(array $data)
    {
        return Validator::make($data, [
            'mobile'        => 'required',
            'verify_number' => 'required',
            'captcha'       => 'required',
        ]);
    }


    /**
     * 管理端登录短信验证码
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function sendSms(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
        ], [
            'mobile.required' => '手机号不能为空',
        ]);

        $mobile = $request->mobile;

        $count = Administrator::query()
            ->where('mobile', $mobile)
            ->count();

        $user = Administrator::query()
            ->where('mobile', $mobile)
            ->orderBy('id')
            ->first();

        if ($count === 0) {
            throw new ResourceException('对不起，该用户未注册管理端');
        }

        $sms = app(SmsUsecase::class);

        if ($count > 1) {
            \Log::error("当前登录的手机号" . $mobile . "有绑定了多个账号");
        }

        $sms->sendSms($mobile, $user->subject_id, 'admin_sms_login');

        return response()->noContent();
    }
}
