<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilePrivate extends Field
{

    use Field\UploadField;

    protected $view = 'admin::form.file';

    /**
     * Css.
     *
     * @var array
     */
    protected static $css = [
        '/vendor/laravel-admin/bootstrap-fileinput/css/fileinput.min.css?v=4.3.7',
    ];

    /**
     * Js.
     *
     * @var array
     */
    protected static $js = [
        '/vendor/laravel-admin/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js?v=4.3.7',
        '/vendor/laravel-admin/bootstrap-fileinput/js/fileinput.min.js?v=4.3.7',
    ];


    /**
     * Create a new File instance.
     *
     * @param string $column
     * @param array  $arguments
     */
    public function __construct($column, $arguments = [])
    {
        $this->disk(config('admin.upload.private_disk'));

        parent::__construct($column, $arguments);
    }


    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.file');
    }


    /**
     * {@inheritdoc}
     */
    public function getValidator(array $input)
    {
        /*
         * If has original value, means the form is in edit mode,
         * then remove required rule from rules.
         */
        if ($this->original()) {
            $this->removeRule('required');
        }

        /*
         * Make input data validatable if the column data is `null`.
         */
        if (array_has($input, $this->column) && is_null(array_get($input, $this->column))) {
            $input[$this->column] = '';
        }

        $rules = $attributes = [];

        if ( ! $fieldRules = $this->getRules()) {
            return false;
        }

        $rules[$this->column] = $fieldRules;
        $attributes[$this->column] = $this->label;

        return Validator::make($input, $rules, [], $attributes);
    }


    /**
     * Prepare for saving.
     *
     * @param UploadedFile|array $file
     *
     * @return mixed|string
     */
    public function prepare($file)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }

        $this->name = $this->getStoreName($file);

        return $this->uploadAndDeleteOriginal($file);
    }


    /**
     * Upload file and delete original file.
     *
     * @param UploadedFile $file
     *
     * @return mixed
     */
    protected function uploadAndDeleteOriginal(UploadedFile $file)
    {
        $this->renameIfExists($file);

        $target = $this->getDirectory() . '/' . $this->name;

        $this->storage->put($target, file_get_contents($file->getRealPath()));

        $this->destroy();

        return $target;
    }


    /**
     * Preview html for file-upload plugin.
     *
     * @return string
     */
    protected function preview()
    {
        return $this->objectUrl($this->value);
    }


    /**
     * Hides the file preview.
     *
     * @return $this
     */
    public function hidePreview()
    {
        return $this->options([
            'showPreview' => false,
        ]);
    }


    /**
     * Initialize the caption.
     *
     * @param string $caption
     *
     * @return string
     */
    protected function initialCaption($caption)
    {
        return basename($caption);
    }


    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        if ( ! empty($this->fileType)) {
            return [
                [ 'caption' => basename($this->value), 'key' => 0, 'filetype' => $this->fileType ],
            ];
        } else {
            return [
                [ 'caption' => basename($this->value), 'key' => 0 ],
            ];
        }
    }


    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->setupDefaultOptions();

        if ( ! empty($this->value)) {
            $this->attribute('data-initial-preview', $this->preview());

            $this->setupPreviewOptions();
        }

        $this->options([ 'overwriteInitial' => true ]);

        $options = json_encode($this->options);

        $this->script = <<<EOT

$("input{$this->getElementClassSelector()}").fileinput({$options});

EOT;

        return parent::render();
    }
}
