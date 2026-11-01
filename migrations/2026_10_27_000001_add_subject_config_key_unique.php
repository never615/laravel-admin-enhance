<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Domain\SubjectConfig\SubjectConfigLiveCache;
use Mallto\Admin\Listeners\Events\SubjectConfigChanged;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            if (DB::connection()->getDriverName() === 'pgsql') {
                // Serialize writers and concurrent Pod migrations through the
                // cleanup and constraint installation, including direct up() calls.
                DB::statement('LOCK TABLE subject_configs IN SHARE ROW EXCLUSIVE MODE');
            }

            foreach (Schema::getIndexes('subject_configs') as $index) {
                if ($index['name'] !== 'subject_configs_subject_key_unique') {
                    continue;
                }
                if (!$index['unique'] || $index['columns'] !== ['subject_id', 'key']) {
                    throw new RuntimeException('项目配置唯一约束的现有索引定义不匹配，请先核对。');
                }
                // A different Pod may have completed this migration while this
                // process was waiting for the table lock.
                // A prior attempt may also have committed the constraint but
                // failed during cache publication, before migration bookkeeping.
                // Reconcile all surviving identities so retry repairs that state.
                DB::connection()->afterCommit(static function (): void {
                    foreach (DB::table('subject_configs')->select('subject_id', 'key')->orderBy('id')->cursor() as $row) {
                        try {
                            app(SubjectConfigLiveCache::class)->invalidate((int) $row->subject_id, $row->key);
                        } finally {
                            event(new SubjectConfigChanged((int) $row->subject_id, $row->key, null, null));
                        }
                    }
                });
                return;
            }

            // Runtime reads include all rows, so deleted_at/type do not scope
            // uniqueness. Keep the largest ID, including its value and visibility,
            // even when legacy duplicates contain conflicting configuration.
            while (true) {
                $duplicates = DB::table('subject_configs')->select('subject_id', 'key')
                    ->groupBy('subject_id', 'key')->havingRaw('COUNT(*) > 1')
                    ->orderBy('subject_id')->orderBy('key')->limit(100)->get();
                if ($duplicates->isEmpty()) {
                    break;
                }

                foreach ($duplicates as $duplicate) {
                    $rows = SubjectConfig::query()->where('subject_id', $duplicate->subject_id)
                        ->where('key', $duplicate->key)->orderByDesc('id')->lockForUpdate()->get();

                    if (DB::connection()->getDriverName() === 'pgsql') {
                        $references = DB::select("SELECT 1 FROM pg_constraint WHERE contype = 'f' AND confrelid = 'subject_configs'::regclass LIMIT 1");
                        if ($references !== []) {
                            throw new RuntimeException('项目配置存在入向外键，无法自动删除重复项，请先核对引用。');
                        }
                    }

                    foreach ($rows->slice(1) as $row) {
                        // Model events replace private/public runtime payloads and
                        // invalidate derived caches only after the transaction commits.
                        if (!$row->delete()) {
                            throw new RuntimeException('重复项目配置删除失败，迁移已回滚。');
                        }
                    }
                }
            }

            Schema::table('subject_configs', function (Blueprint $table): void {
                $table->unique(['subject_id', 'key'], 'subject_configs_subject_key_unique');
            });
        });
    }

    public function down(): void
    {
        Schema::table('subject_configs', function (Blueprint $table): void {
            $table->dropUnique('subject_configs_subject_key_unique');
        });
    }
};
