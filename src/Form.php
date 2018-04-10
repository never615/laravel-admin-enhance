<?php
/**
 * Copyght (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;



class Form extends \Encore\Admin\Form
{
    /**
     * Get current resource route url.
     *
     * @param int $slice
     *
     * @return string
     */
    public function resource($slice = -2)
    {
        $uri = app('request')->getUri();
        $uri = preg_replace("/^http:/i", "https:", $uri);
        $segments = explode('/', trim($uri, '/'));

        if ($slice != 0) {
            $segments = array_slice($segments, 0, $slice);
        }

        return implode('/', $segments);
    }
}
