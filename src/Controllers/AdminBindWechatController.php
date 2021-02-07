<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

    /**
     * 绑定微信
     *
     * @param Request       $request
     * @param WechatUsecase $wechatUsecase
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function bindWechat(Request $request, WechatUsecase $wechatUsecase)
    {
        //获取微信回调的参数
        $encryOpenid = $request->openid;
        //拿出openid
        $openid = $this->decryptOpenid($encryOpenid);

        //查询当前需要绑定的用户
        $waiteBindAdminUser = Administrator::find($request->admin_user_id);

        //如果没有需要绑定的用户抛异常
        if ( ! $waiteBindAdminUser) {
            throw new ResourceException("无效请求");
        }

        //获取绑定用户的主体
        $subject = $waiteBindAdminUser->subject;

        //获取相关微信用户
        $wechatUserInfo = $wechatUsecase->getUserInfo(
            SubjectUtils::getConfigByOwner(
                SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                $subject, $subject->uuid),
            $openid);


        if ( ! $wechatUserInfo) {
            throw new ResourceException("未找到相应微信用户");
        }

        //检查当前账户是否绑定了其他微信
        //if ($waiteBindAdminUser->openid) {
        //    throw new ResourceException("当前账号(" . $waiteBindAdminUser->username . ")已经绑定其他微信,如果想重新绑定,需要先解绑微信");
        //}

        //检查并移除该微信的其他账号绑定关系
        $adminUser = Administrator::query()
            ->where("subject_id", $waiteBindAdminUser->subject_id)
            ->where("openid->" . $openid . "->openid", $openid)
            ->where("id", "!=", $waiteBindAdminUser->id)
            ->first();

        if ($adminUser) {
            $adminUser->openid = Arr::except($adminUser->openid, $openid);

            $adminUser->save();
        }

        //绑定
        $waiteBindAdminUser->openid = Arr::add($waiteBindAdminUser->openid, $openid, $wechatUserInfo->toArray());
        $waiteBindAdminUser->save();

        echo "<h1>绑定成功</h1>";
    }


    /**
     * 解绑微信
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function unbindWechat(Request $request)
    {
        $openid = $request->openid;
        $adminUser = Administrator::find($request->id);

        $adminUser->openid = Arr::except($adminUser->openid, $openid);
        $adminUser->save();

        return response()->nocontent();
    }
}
