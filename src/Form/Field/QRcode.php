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


    /**
     * @return mixed
     */
    public function getQrcodeUrl()
    {

        if ($this->qrcodeUrl instanceof \Closure) {
            $this->qrcodeUrl = $this->qrcodeUrl->call($this->form->model(), $this->value);
        }

        $this->qrcodeUrl = urlencode($this->qrcodeUrl);

        $baseUrl = config("app.url");

        //return "<a target='_blank' href='$baseUrl/api/qr_image?size=150x150&data={$this->qrcodeUrl}' download='w3logo' '>下载</a>";
        return "<img src='$baseUrl/api/qr_image?size=150x150&data={$this->qrcodeUrl}' style='height: 150px;width: 150px;'/>";
    }


    public function qrcodeUrl($url)
    {
        $this->qrcodeUrl = $url;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return parent::fieldRender([
            'qrcodeUrl' => $this->getQrcodeUrl(),
        ]);
    }

}
