<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

/**
 * 扫描二维码
 * Class ScanQRButton
 *
 * @package Mallto\Admin\Grid\Tools
 */
class ScanQRButton extends AbstractTool
{

    private $view = "adminE::grid.tools.scan_qr";


    /**
     * Get view of this field.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }


    /**
     * Render Export button.
     *
     * @return string
     */
    public function render()
    {
        return view($this->getView());
    }
}
