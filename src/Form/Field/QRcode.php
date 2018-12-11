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
//            $this->qrcodeUrl = call_user_func($this->qrcodeUrl, $this->form);
            $this->qrcodeUrl = $this->qrcodeUrl->call($this->form->model(), $this->value);
        }

        $baseUrl = config("app.url");

        return "<img src='$baseUrl/api/qr_image?size=150x150&data={$this->qrcodeUrl}' style='height: 150px;width: 150px;'/>";
    }

    /**
     * @param mixed $qrcodeUrl
     */
    public function setQrcodeUrl($qrcodeUrl): void
    {
        $this->qrcodeUrl = $qrcodeUrl;
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
        return parent::render()->with([
            'qrcodeUrl' => $this->getQrcodeUrl(),
        ]);
    }


}
