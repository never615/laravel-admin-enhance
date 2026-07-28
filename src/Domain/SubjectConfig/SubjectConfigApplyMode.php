<?php

namespace Mallto\Admin\Domain\SubjectConfig;

final class SubjectConfigApplyMode
{
    public const SNAPSHOT_RELOAD = 'snapshot_reload';

    public const LIVE_CACHED = 'live_cached';

    public static function normalize(?string $mode): string
    {
        if (in_array($mode, [self::SNAPSHOT_RELOAD, self::LIVE_CACHED], true)) {
            return $mode;
        }

        return self::SNAPSHOT_RELOAD;
    }

    public static function label(?string $mode): string
    {
        return match (self::normalize($mode)) {
            self::LIVE_CACHED => '实时缓存，保存后生效',
            default => '进程快照，发布重启后生效',
        };
    }
}
