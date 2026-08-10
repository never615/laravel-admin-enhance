<?php

namespace Mallto\Admin\Observers;

use Illuminate\Support\Facades\DB;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Domain\SubjectConfig\SubjectConfigDefinitions;
use Mallto\Admin\Listeners\Events\SubjectConfigChanged;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigIdentity;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigInvalidator;
use Throwable;

class SubjectConfigObserver
{
    public function saving(SubjectConfig $subjectConfig): void
    {
        if (!$subjectConfig->exists || $subjectConfig->isDirty(['subject_id', 'key', 'value', 'type'])) {
            $subjectConfig->runtimeConfigChangeContext = [
                'subject_id' => (int) ($subjectConfig->getOriginal('subject_id') ?: $subjectConfig->subject_id),
                'key' => (string) ($subjectConfig->getOriginal('key') ?: $subjectConfig->key),
                'created' => !$subjectConfig->exists,
            ];
        }
    }

    public function saved(SubjectConfig $subjectConfig): void
    {
        $this->replaceAfterCommit($subjectConfig, false);
    }

    public function deleted(SubjectConfig $subjectConfig): void
    {
        $this->replaceAfterCommit($subjectConfig, true);
    }

    private function replaceAfterCommit(SubjectConfig $subjectConfig, bool $force): void
    {
        $savingContext = $subjectConfig->runtimeConfigChangeContext;
        $subjectConfig->runtimeConfigChangeContext = null;
        if (!$force
            && !$subjectConfig->wasRecentlyCreated
            && !$subjectConfig->wasChanged(['subject_id', 'key', 'value', 'type'])
            && !$subjectConfig->isDirty(['subject_id', 'key', 'value', 'type'])
            && $savingContext === null) {
            return;
        }

        $currentSubjectId = (int) $subjectConfig->subject_id;
        $currentKey = (string) $subjectConfig->key;
        $originalSubjectId = $savingContext['subject_id']
            ?? (int) ($subjectConfig->getOriginal('subject_id') ?: $currentSubjectId);
        $originalKey = $savingContext['key']
            ?? (string) ($subjectConfig->getOriginal('key') ?: $currentKey);
        $identityMoved = !$force
            && !($savingContext['created'] ?? false)
            && ($originalSubjectId !== $currentSubjectId || $originalKey !== $currentKey);
        $eventOriginalSubjectId = $identityMoved ? $originalSubjectId : null;
        $eventOriginalKey = $identityMoved ? $originalKey : null;
        $identities = [];

        foreach ([
            ['subject_id' => $originalSubjectId, 'key' => $originalKey],
            ['subject_id' => $currentSubjectId, 'key' => $currentKey],
        ] as $identity) {
            if ($identity['subject_id'] > 0 && $identity['key'] !== '') {
                $identities[$identity['subject_id']."\0".$identity['key']] = $identity;
            }
        }

        if ($identities === []) {
            return;
        }

        $connectionName = $subjectConfig->getConnectionName();
        DB::connection($connectionName)->afterCommit(static function () use (
            $identities,
            $connectionName,
            $currentSubjectId,
            $currentKey,
            $originalSubjectId,
            $originalKey,
            $eventOriginalSubjectId,
            $eventOriginalKey
        ): void {
            $invalidator = app(RuntimeConfigInvalidator::class);
            $failure = null;

            try {
                foreach ($identities as $identity) {
                    $subjectId = $identity['subject_id'];
                    $key = $identity['key'];
                    $mode = SubjectConfigDefinitions::applyModeFor($key);
                    $private = SubjectConfig::on($connectionName)
                        ->where('subject_id', $subjectId)
                        ->where('key', $key)
                        ->orderBy('id')
                        ->first();
                    $public = SubjectConfig::on($connectionName)
                        ->where('subject_id', $subjectId)
                        ->whereIn('type', ['public', 'front'])
                        ->where('key', $key)
                        ->orderBy('id')
                        ->first();

                    $invalidator->replaceAfterCommit(
                        RuntimeConfigIdentity::subjectPrivate($subjectId, $key),
                        $mode,
                        ['found' => $private !== null, 'value' => $private?->value]
                    );
                    $invalidator->replaceAfterCommit(
                        RuntimeConfigIdentity::subjectPublic($subjectId, $key),
                        $mode,
                        ['found' => $public !== null, 'value' => $public?->value]
                    );
                }
            } catch (Throwable $exception) {
                $failure = $exception;
            }

            try {
                event(new SubjectConfigChanged(
                    $currentSubjectId,
                    $currentKey,
                    $eventOriginalSubjectId,
                    $eventOriginalKey
                ));
            } catch (Throwable $eventException) {
                if ($failure === null) {
                    throw $eventException;
                }
            }

            if ($failure !== null) {
                throw $failure;
            }
        });
    }

}
