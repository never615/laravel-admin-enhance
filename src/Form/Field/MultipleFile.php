<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleFile extends Field\MultipleFile
{
    use UploadField2;
    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        $files = $this->value ?: [];

        $config = [];

        if (!empty($this->fileType)) {
            foreach ($files as $index => $file) {
                $config[] = [
                    'caption'  => basename($file),
                    'key'      => $index,
                    'filetype' => $this->fileType,
                ];
            }
        } else {
            foreach ($files as $index => $file) {
                $config[] = [
                    'caption' => basename($file),
                    'key'     => $index,
                ];
            }
        }


        return $config;
    }
}
