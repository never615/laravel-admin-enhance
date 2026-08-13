<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_menu_subjects')) {
            Schema::create('admin_menu_subjects', function (Blueprint $table): void {
                $table->unsignedInteger('admin_menu_id');
                $table->unsignedInteger('subject_id');
                $table->primary(['admin_menu_id', 'subject_id']);
            });
        }

        if (! Schema::hasColumn('admin_menu', 'sub_title')) {
            Schema::table('admin_menu', function (Blueprint $table): void {
                $table->string('sub_title')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Repair migrations must preserve historical schema on rollback.
    }
};
