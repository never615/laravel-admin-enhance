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
use Mallto\User\Domain\WechatUsecase;

/**
 * 管理端使用:绑定微信和解绑微信
 * Class AdminBindWechatController
 *
 * @package Mallto\Admin\Controllers
 */
class AdminBindWechatController extends Controller
{
    use OpenidCheckTrait;

    public function bindWechat(Request $request, WechatUsecase $wechatUsecase)
    {
        $encryOpenid = $request->openid;
        $openid = $this->decryptOpenid($encryOpenid);


        $adminUser = Administrator::find($request->admin_user_id);
        if (!$adminUser) {
            throw new ResourceException("无效请求");
        }

        $wechatUserInfo = $wechatUsecase->getUserInfo($adminUser->subject->uuid, $openid);

        if (!$wechatUserInfo) {
            throw new ResourceException("未找到相应微信用户");
        }



        //检查并移除该微信的其他账号绑定关系
        Administrator::where("subject_id", $adminUser->subject_id)
            ->where("openid->openid", $openid)
            ->where("id", "!=", $adminUser->id)
            ->update([
                "openid" => null,
            ]);

        //绑定
        $adminUser->openid = $wechatUserInfo->toArray();
        $adminUser->save();

        echo "<h1>绑定成功</h1>";
    }


    public function unbindWechat(Request $request)
    {
        $adminUser = Administrator::find($request->id);

        $adminUser->openid = null;
        $adminUser->save();

        return response()->nocontent();
    }
}
