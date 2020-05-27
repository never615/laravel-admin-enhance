<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;

class QiniuMultipleFile extends Field\MultipleFile
{

    protected $view = 'adminE::form.qiniu_multiplefile';


    /**
     * Prepare for saving.
     *
     * 前端只提交了文件路径,只需要保存文件
     *
     * @param $filePaths
     *
     * @return mixed|string
     */
    public function prepare($filePaths)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy(request(static::FILE_DELETE_FLAG));
        }

        if ( ! is_null($filePaths)) {
            $filePaths = explode(",", $filePaths);
        } else {
            $filePaths = [];
        }

        $result = array_merge($this->original(), $filePaths);

        return $result;
    }


    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        $files = $this->value ?: [];

        $config = [];

        foreach ($files as $index => $file) {
            $config[] = [
                'caption'  => basename($file),
                'key'      => $index,
                'filetype' => $this->options['filetype'] ?? "",
            ];
        }

        return $config;
    }


    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->attribute('multiple', true);

//        $this->options(['overwriteInitial' => true]);
        $this->setupDefaultOptions();

        if ( ! empty($this->value)) {
            $this->options([ 'initialPreview' => $this->preview() ]);
            $this->options([ 'initialPreviewConfig' => $this->initialPreviewConfig() ]);

            $this->setupPreviewOptions();
        }

        $options = json_encode($this->options);

        $this->script = <<<EOT

$("input{$this->getElementClassSelector()}").fileinput({$options});

EOT;

        return Field::render();
    }
}
