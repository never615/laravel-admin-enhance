<?php

namespace Mallto\Admin\Listeners\Events;

final class SubjectConfigChanged
{
    public function __construct(
        public readonly int $subjectId,
        public readonly string $key,
        public readonly ?int $originalSubjectId,
        public readonly ?string $originalKey
    ) {
    }
}
