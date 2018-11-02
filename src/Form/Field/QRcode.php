<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;

class QRcode extends Field
{
    protected $view = 'adminE::form.qrcode';

    protected $qrcodeUrl;

    public function qrcodeUrl($url)
    {
        $baseUrl = config("app.url");

//        $qrcode = "<img src='https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$url}' style='height: 150px;width: 150px;'/>";
        $qrcode = "<img src='$baseUrl/api/qr_image?size=150x150&data={$url}' style='height: 150px;width: 150px;'/>";

        $this->qrcodeUrl = $qrcode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return parent::render()->with([
            'qrcodeUrl' => $this->qrcodeUrl,
        ]);
    }


}
