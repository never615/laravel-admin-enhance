<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\SubjectUtils;
use Mallto\Mall\SubjectConfigConstants;
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

        $waiteBindAdminUser = Administrator::find($request->admin_user_id);
        if ( ! $waiteBindAdminUser) {
            throw new ResourceException("无效请求");
        }

        $subject = $waiteBindAdminUser->subject;

        $wechatUserInfo = $wechatUsecase->getUserInfo(
            SubjectUtils::getConfigByOwner(SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                $subject),
            $openid);

        if ( ! $wechatUserInfo) {
            throw new ResourceException("未找到相应微信用户");
        }

        if ($waiteBindAdminUser->openid) {
            throw new ResourceException("当前账号(" . $waiteBindAdminUser->username . ")已经绑定其他微信,如果想重新绑定,需要先解绑微信");
        }

        //检查并移除该微信的其他账号绑定关系
        Administrator::where("subject_id", $waiteBindAdminUser->subject_id)
            ->where("openid->openid", $openid)
            ->where("id", "!=", $waiteBindAdminUser->id)
            ->update([
                "openid" => null,
            ]);

        //绑定
        $waiteBindAdminUser->openid = $wechatUserInfo->toArray();
        $waiteBindAdminUser->save();

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
