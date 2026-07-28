<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubjectConfigLiveCache
{
    /**
     * @param callable(): array{found: bool, value: mixed} $loader
     * @return array{found: bool, value: mixed, source: string}
     */
    public function remember(int $subjectId, string $key, callable $loader): array
    {
        $cacheKey = $this->cacheKey($subjectId, $key);

        try {
            $cached = $this->store()->get($cacheKey);
            if ($this->isValidPayload($cached, $subjectId, $key)) {
                return [
                    'found' => $cached['found'],
                    'value' => $cached['value'],
                    'source' => 'cache',
                ];
            }
        } catch (Throwable $exception) {
            $this->logCacheFailure('read', $subjectId, $key, $exception);
        }

        $loaded = $loader();
        $payload = [
            'subject_id' => $subjectId,
            'key' => $key,
            'found' => (bool)($loaded['found'] ?? false),
            'value' => $loaded['value'] ?? null,
        ];

        try {
            $this->store()->put(
                $cacheKey,
                $payload,
                max(1, (int)config('subject_config_runtime.live_cache.ttl_seconds', 60))
            );
        } catch (Throwable $exception) {
            $this->logCacheFailure('write', $subjectId, $key, $exception);
        }

        return [
            'found' => $payload['found'],
            'value' => $payload['value'],
            'source' => 'database',
        ];
    }

    /**
     * @return array{shared_forget_succeeded: bool, warnings: array<int, string>}
     */
    public function invalidate(int $subjectId, string $key): array
    {
        try {
            $this->store()->forget($this->cacheKey($subjectId, $key));

            return [
                'shared_forget_succeeded' => true,
                'warnings' => [],
            ];
        } catch (Throwable $exception) {
            $warning = sprintf(
                'subject config live cache invalidate failed: subject_id=%d key=%s exception=%s message=%s',
                $subjectId,
                $key,
                $exception::class,
                $exception->getMessage()
            );
            Log::warning($warning);

            return [
                'shared_forget_succeeded' => false,
                'warnings' => [$warning],
            ];
        }
    }

    protected function store()
    {
        return Cache::store((string)config('subject_config_runtime.live_cache.store', 'redis'));
    }

    private function cacheKey(int $subjectId, string $key): string
    {
        return 'subject_config:live:' . $subjectId . ':' . sha1($key);
    }

    private function isValidPayload($payload, int $subjectId, string $key): bool
    {
        return is_array($payload)
            && array_key_exists('subject_id', $payload)
            && array_key_exists('key', $payload)
            && array_key_exists('found', $payload)
            && array_key_exists('value', $payload)
            && (int)$payload['subject_id'] === $subjectId
            && $payload['key'] === $key
            && is_bool($payload['found']);
    }

    private function logCacheFailure(string $action, int $subjectId, string $key, Throwable $exception): void
    {
        Log::warning(sprintf(
            'subject config live cache %s failed: subject_id=%d key=%s exception=%s message=%s',
            $action,
            $subjectId,
            $key,
            $exception::class,
            $exception->getMessage()
        ));
    }
}
