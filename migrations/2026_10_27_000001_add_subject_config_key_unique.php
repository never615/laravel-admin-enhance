<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Never choose or delete business configuration values during an upgrade.
        // Runtime reads include all rows, so deleted_at/type are not uniqueness scopes.
        $duplicates = DB::table('subject_configs')->select('subject_id', 'key')
            ->groupBy('subject_id', 'key')->havingRaw('COUNT(*) > 1')
            ->orderBy('subject_id')->orderBy('key')->limit(20)->get();
        if ($duplicates->isNotEmpty()) {
            $identities = $duplicates->map(static function ($row): string {
                $ids = DB::table('subject_configs')->where('subject_id', $row->subject_id)
                    ->where('key', $row->key)->orderBy('id')->pluck('id')->implode(', ');
                return 'subject=' . $row->subject_id . ', key=' . $row->key . ', IDs=' . $ids;
            })->implode('; ');
            throw new RuntimeException('配置唯一约束未安装：请先人工核对并处理重复配置（最多显示 20 组）：' . $identities);
        }

        Schema::table('subject_configs', function (Blueprint $table): void {
            $table->unique(['subject_id', 'key'], 'subject_configs_subject_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subject_configs', function (Blueprint $table): void {
            $table->dropUnique('subject_configs_subject_key_unique');
        });
    }
};
