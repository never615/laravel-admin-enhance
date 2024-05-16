<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Tool\Utils\RequestUtils;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 21/04/2017
 * Time: 5:13 PM
 */
abstract class BaseModel extends Model
{

    use DynamicData, SelectSource;

    protected $hidden = ['deleted_at'];

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }


    public function subjectSetting()
    {
        return $this->belongsTo(SubjectSetting::class, 'subject_id', 'subject_id');
    }

    public function icon(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (empty($value)) {
                    return null;
                }
                if (Str::startsWith($value, "http")) {
                    return $value;
                }

                $url = config("app.file_url_prefix") . $value;
                if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
                    return $url;
                } else {
                    return $url . '?imageView2/0/interlace/1/q/75|imageslim';
                }
            }
        );
    }

    public function logo(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (empty($value)) {
                    return null;
                }
                if (Str::startsWith($value, "http")) {
                    return $value;
                }

                $url = config("app.file_url_prefix") . $value;
                if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
                    return $url;
                } else {
                    return $url . '?imageView2/0/interlace/1/q/75|imageslim';
                }
            }
        );
    }
    public function image(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (empty($value)) {
                    return null;
                }
                if (Str::startsWith($value, "http")) {
                    return $value;
                }

                $url = config("app.file_url_prefix") . $value;
                if (AdminUtils::isAdminRequest() || str_contains($url, "?")) {
                    return $url;
                } else {
                    return $url . '?imageView2/0/interlace/1/q/75|imageslim';
                }
            }
        );
    }
    public function images(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $values = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                if ($values && count($values) > 0) {
                    foreach ($values as $key => $value) {
                        if (Str::startsWith($value, "http")) {
                            $values[$key] = $value;
                        } else {
                            $url = config("app.file_url_prefix") . $value;
                            if (AdminUtils::isAdminRequest() || Str::contains($url, "?")) {
                                $values[$key] = $url;
                            } else {
                                $values[$key] = $url . '?imageView2/0/interlace/1/q/75|imageslim';
                            }
                        }
                    }
                } else {
                    return [];
                }

                return $values;
            },
            set: function ($values) {
                foreach ($values as &$value) {
                    if (Str::startsWith($value, config("app.file_url_prefix"))) {
                        $url = str_replace(config("app.file_url_prefix"), "", $value);
                        if (Str::contains($url, "?")) {
                            $tmpUrls = explode("?", $url);
                            $url = $tmpUrls[0];
                        }
                        $value = $url;
                    }
                }
                $this->attributes['images'] = json_encode(array_values($values), JSON_THROW_ON_ERROR);
            }
        );
    }

    public function admin()
    {
        return $this->belongsTo(Administrator::class, "admin_user_id");
    }

    public function scopeWithLocalizedName($query, $suffix = 'name')
    {
        $language = RequestUtils::getLan();

        if ($language) {
            $localizedName = "{$language}_{$suffix}";
            $query->addSelect(DB::raw("COALESCE(\"$localizedName\", \"$suffix\") as \"$suffix\""));
        }
    }


    public function getLocalizeColumnName($suffix = 'name')
    {
        $language = RequestUtils::getLan();

        if ($language) {
            return "{$language}_{$suffix}";
        } else {
            return $suffix;
        }
    }


}
