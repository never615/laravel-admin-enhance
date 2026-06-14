<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data;

use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Traits\BaseModel;
use Mallto\Admin\Exception\SubjectConfigException;

class SubjectConfig extends BaseModel
{
    private const SAVE_LOCATOR_STATION_PREFIX = 'sa_lo_st_';

    const TYPE = [
        'public'  => '公共配置',
        'private' => '私有配置',
        'front'   => '前端配置',
    ];

    protected static function booted(): void
    {
        static::saving(function (SubjectConfig $subjectConfig) {
            $subjectConfig->assertSaveLocatorStationLimit();
        });
    }

    private function assertSaveLocatorStationLimit(): void
    {
        $key = (string)$this->key;
        if (!str_starts_with($key, self::SAVE_LOCATOR_STATION_PREFIX)) {
            return;
        }

        $subjectId = (int)$this->subject_id;
        if ($subjectId <= 0) {
            return;
        }

        if ($this->exists
            && (string)$this->getOriginal('key') === $key
            && (int)$this->getOriginal('subject_id') === $subjectId) {
            return;
        }

        $query = self::query()
            ->where('subject_id', $subjectId)
            ->where('key', 'like', self::SAVE_LOCATOR_STATION_PREFIX . '%');

        if (Schema::hasColumn($this->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        $existingKeys = $query->pluck('key')
            ->map(fn($value) => (string)$value)
            ->unique()
            ->values();

        if ($existingKeys->contains($key)) {
            return;
        }

        $limit = max(1, (int)config('subject_config_runtime.sa_lo_st_limit', 10));
        if ($existingKeys->count() >= $limit) {
            throw new SubjectConfigException('临时保存定位基站开关最多 ' . $limit . ' 个，请删除不再使用的配置后再新增。');
        }
    }

}
