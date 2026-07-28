<?php

namespace Mallto\Admin\Observers;

use Illuminate\Support\Facades\DB;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\SubjectUtils;

class SubjectConfigObserver
{
    public function saved(SubjectConfig $subjectConfig): void
    {
        $this->invalidateAfterCommit($subjectConfig);
    }

    public function deleted(SubjectConfig $subjectConfig): void
    {
        $this->invalidateAfterCommit($subjectConfig);
    }

    private function invalidateAfterCommit(SubjectConfig $subjectConfig): void
    {
        $identities = [];
        foreach ([
            [
                'subject_id' => (int)$subjectConfig->getOriginal('subject_id'),
                'key' => (string)$subjectConfig->getOriginal('key'),
            ],
            [
                'subject_id' => (int)$subjectConfig->subject_id,
                'key' => (string)$subjectConfig->key,
            ],
        ] as $identity) {
            if ($identity['subject_id'] <= 0 || $identity['key'] === '') {
                continue;
            }

            $identities[$identity['subject_id'] . "\0" . $identity['key']] = $identity;
        }

        if ($identities === []) {
            return;
        }

        DB::connection($subjectConfig->getConnectionName())->afterCommit(
            static function () use ($identities): void {
                foreach ($identities as $identity) {
                    SubjectUtils::clearDynamicConfig($identity['key'], $identity['subject_id']);
                }
            }
        );
    }
}
