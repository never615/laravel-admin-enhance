<?php

namespace Mallto\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;
use Mallto\Mall\Data\UserCoupon;
use Mallto\Mall\Domain\VerificationUsecase;
use Mallto\Mall\Exception\ExchangeCodeNotFoundException;

class VerificationCoupon extends RowAction
{

    /**
     * 在页面点击这一列的图表之后，发送请求到后端的handle方法执行
     *
     * @param UserCoupon $userCoupon
     *
     * @return \Encore\Admin\Actions\Response
     */
    public function handle(UserCoupon $userCoupon)
    {
        $verificationUsecase = app(VerificationUsecase::class);

        try {
            $verificationUsecase->getByCode($userCoupon->exchange_code, true);
        } catch (\Exception $exception) {
            throw new ExchangeCodeNotFoundException('该记录未找到！');
        }

        $verificationUsecase->verify($userCoupon->exchange_code);

        return $this->response()->success('核销成功')->refresh();
    }


    /**
     * 弹窗
     */
    public function dialog()
    {
        $this->confirm('确定核销该卡券？');
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
