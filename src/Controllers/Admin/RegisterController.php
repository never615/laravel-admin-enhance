<?php
/*
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Listeners\Events\SubjectSaved;
use Mallto\Tool\Exception\ResourceException;

/**
 * User: never615 <never615.com>
 * Date: 2023/10/9
 * Time: 18:46
 */
class RegisterController extends Controller
{

    public function getRegister()
    {
        return view('vendor.admin.register');
    }


    public function postRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'              => [ 'required', 'unique:admin_users,username' ],
            'password'              => 'required',
            'password_confirmation' => 'required',
            'company'               => [ 'required', 'unique:subjects,name' ],
            'mobile'                => [ 'required', 'unique:admin_users,mobile', 'mobile' ],
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $company = $request->company;
        $mobile = $request->mobile;
        $username = $request->username;
        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        if ($password != $password_confirmation) {
            throw new ResourceException('两次密码不一致,请检查');
        }

        //创建主体
        $subject = Subject::query()->create([
            'name' => $company,
        ]);

        Subject::query()->where('id', $subject->id)->update([
            'uuid' => "1".sprintf('%06d', $subject->id),
        ]);

        //
        //if (strlen($crypt_password) >= 20) {
        //    $key = "1E390CMD585LLS4S"; //与JS端的KEY一致
        //    $iv = "1104432290129056"; //这个也是要与JS中的IV一致
        //    $credentials['password'] = openssl_decrypt(base64_decode($credentials['password']), "AES-128-CBC",
        //        $key, OPENSSL_RAW_DATA, $iv);
        //}

        event(new SubjectSaved($subject->id, true, false, [
            'username' => $username,
            'password' => $password,
            'mobile'   => $mobile,
        ]));

        session([ 'login_msg' => '注册成功,请登录' ]);

        return redirect()->intended($this->redirectPath());
    }


    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix').'/auth/login';
    }
}