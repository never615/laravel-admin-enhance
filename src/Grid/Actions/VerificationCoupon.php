<?php

namespace Mallto\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mallto\Mall\Data\UserCoupon;
use Mallto\Mall\Domain\VerificationUsecase;

class VerificationCoupon extends RowAction
{

    /**
     * @param UserCoupon $userCoupon
     * @param            $request
     *
     * @return \Encore\Admin\Actions\Response
     */
    public function handle(UserCoupon $userCoupon, Request $request)
    {
        $verificationUsecase = app(VerificationUsecase::class);

        $adminUser = Auth::guard("admin")->user();

        $verificationUsecase->verify($userCoupon->exchange_code, $adminUser, 'admin', false,
            $request->verify_count);

        return $this->response()->success('核销成功')->refresh();
    }


    /**
     * swal2需要弹出的表单内容
     */
    public function form()
    {
        $this->integer('verify_count', '请输入核销数量')->default(1)->rules("required")->style('text-align',
            'center')->help('请输入该核销码剩余核销次数以内的数字！');
    }


    /**
     * 列的内容显示
     *
     * @param $value
     *
     * @return string|void
     */
    public function display($value)
    {
        return "<button style='background-color: #00c0ef; border-color:#00acd6; border-radius:3px; color: white' >核销</button>";
    }
}
