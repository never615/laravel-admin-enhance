<?php


namespace Mallto\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mallto\Mall\Data\AdminUser;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Domain\SmsUsecase;

class AuthController extends BaseAuthController
{
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function smsLogin(Request $request)
    {
        $remember = $request->get('remember', false);

        $this->loginSmsValidator($request->all())->validate();

        $adminUser = Adminuser::query()->where('mobile', $request->mobile)->first();

        $sms = app(SmsUsecase::class);

        try {
            $sms->checkVerifyCode($request->mobile, $request->verify_number, 'admin_sms_login', $adminUser->subject_id);
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

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
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
        ]);
    }

    /**
     * 管理端登录短信验证码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendSms(Request $request)
    {
        $mobile = $request->mobile;

        $count = AdminUser::query()
            ->where('mobile', $mobile)
            ->count();

        $user = AdminUser::query()
            ->where('mobile', $mobile)
            ->orderBy('id')
            ->first();

        if ($count === 0) {
            throw new ResourceException('对不起，该用户未注册管理端');
        }

        $sms = app(SmsUsecase::class);

        if ($count > 1) {
            \Log::error("当前登录的手机号".$mobile."有绑定了多个账号");
        }

        $sms->sendSms($mobile, $user->subject_id, 'admin_sms_login');

        return response()->noContent();
    }
}
