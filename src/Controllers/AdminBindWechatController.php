<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\Data\Administrator;
use Mallto\Tool\Exception\ResourceException;
use Mallto\User\Domain\Traits\OpenidCheckTrait;
use Overtrue\LaravelWeChat\Model\WechatUserInfoRepository;


class AdminBindWechatController extends Controller
{

    use OpenidCheckTrait;

    public function bindWechat(Request $request, WechatUserInfoRepository $wechatUserInfoRepository)
    {
        $encryOpenid = $request->openid;
        $openid = $this->decryptOpenid($encryOpenid);


        $adminUser = Administrator::find($request->admin_user_id);
        if (!$adminUser) {
            throw new ResourceException("无效请求");
        }


        $wechatUserInfo = $wechatUserInfoRepository->getWechatUserInfo($adminUser->subject->uuid, $openid);
        if ($wechatUserInfo) {
            if (!$adminUser) {
                throw new ResourceException("未找到响应微信用户");
            }
        }


        $adminUser->openid = $wechatUserInfo->toArray();
        $adminUser->save();


        echo "<h1>绑定成功</h1>";
    }
}
