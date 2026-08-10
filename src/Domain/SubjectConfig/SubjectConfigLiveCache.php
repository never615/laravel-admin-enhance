<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Mallto\Admin\Data\SubjectConfig;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigCache;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigIdentity;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigInvalidator;

class SubjectConfigLiveCache
{
    public function __construct(
        private readonly RuntimeConfigCache $cache,
        private readonly RuntimeConfigInvalidator $invalidator
    ) {
    }

    /**
     * Backward-compatible private-scope adapter.
     *
     * @param callable(): array{found: bool, value: mixed} $loader
     * @return array{found: bool, value: mixed, source: string}
     */
    public function remember(int $subjectId, string $key, callable $loader): array
    {
        $loaded = false;
        $result = $this->rememberPrivate(
            $subjectId,
            $key,
            SubjectConfigDefinitions::applyModeFor($key),
            static function () use ($loader, &$loaded): array {
                $loaded = true;

                return $loader();
            }
        );

        return $result + ['source' => $loaded ? 'database' : 'cache'];
    }

    /** @param callable(): array{found: bool, value: mixed} $loader */
    public function rememberPrivate(int $subjectId, string $key, string $mode, callable $loader): array
    {
        return $this->cache->remember(RuntimeConfigIdentity::subjectPrivate($subjectId, $key), $mode, $loader);
    }

    /** @param callable(): array{found: bool, value: mixed} $loader */
    public function rememberPublic(int $subjectId, string $key, string $mode, callable $loader): array
    {
        return $this->cache->remember(RuntimeConfigIdentity::subjectPublic($subjectId, $key), $mode, $loader);
    }

    /** @return array{shared_forget_succeeded: bool, warnings: array<int, string>} */
    public function invalidate(int $subjectId, string $key): array
    {
        $mode = SubjectConfigDefinitions::applyModeFor($key);
        $private = SubjectConfig::query()
            ->where('subject_id', $subjectId)
            ->where('key', $key)
            ->orderBy('id')
            ->first();
        $public = SubjectConfig::query()
            ->where('subject_id', $subjectId)
            ->whereIn('type', ['public', 'front'])
            ->where('key', $key)
            ->orderBy('id')
            ->first();

        $this->invalidator->replaceAfterCommit(
            RuntimeConfigIdentity::subjectPrivate($subjectId, $key),
            $mode,
            ['found' => $private !== null, 'value' => $private?->value]
        );
        $this->invalidator->replaceAfterCommit(
            RuntimeConfigIdentity::subjectPublic($subjectId, $key),
            $mode,
            ['found' => $public !== null, 'value' => $public?->value]
        );

        return [
            'shared_forget_succeeded' => true,
            'warnings' => [],
        ];
    }
}
