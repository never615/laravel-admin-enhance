<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Domain\Traits\AuthValidateTrait;
use Mallto\User\Domain\Traits\OpenidCheckTrait;

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

        $adminUser = Administrator::where("subject_id", $subject->id)
            ->where("openid->openid", $openid)
            ->first();
        if (!$adminUser) {
            throw new ResourceException("当前微信未绑定管理账号,请前往管理后台绑定");
        }


        $token = $adminUser->createToken("admin_api");
        $adminUser->token = $token->accessToken;

//        if ($adminUser->adminable_type == "shop") {
//            $shop = Shop::find($adminUser->adminable_id);
//        }


        return response()->json($adminUser->only([
            "id",
            "name",
            "username",
            "token"
        ]));


    }

}
