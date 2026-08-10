<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigCacheMode;

final class SubjectConfigApplyMode
{
    public const SWOOLE_REDIS = RuntimeConfigCacheMode::SWOOLE_REDIS;

    public const SHARED_REDIS = RuntimeConfigCacheMode::SHARED_REDIS;

    public const BOOTSTRAP_RELOAD = RuntimeConfigCacheMode::BOOTSTRAP_RELOAD;

    public const LIVE_CACHED = self::SHARED_REDIS;

    public const SNAPSHOT_RELOAD = self::SWOOLE_REDIS;

    public static function normalize(?string $mode): string
    {
        if ($mode === 'snapshot_reload') {
            return self::SWOOLE_REDIS;
        }

        if ($mode === 'live_cached') {
            return self::SHARED_REDIS;
        }

        $normalized = RuntimeConfigCacheMode::normalize($mode, self::SWOOLE_REDIS);

        return $normalized === self::SHARED_REDIS ? self::SHARED_REDIS : self::SWOOLE_REDIS;
    }

    public static function label(?string $mode): string
    {
        return match (self::normalize($mode)) {
            self::SHARED_REDIS => '共享 Redis，保存后实时生效',
            default => 'Swoole + Redis，保存后实时生效',
        };
    }
}
