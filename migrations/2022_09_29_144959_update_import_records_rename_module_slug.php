<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;

/**
 * 添加主体第三方项目标识
 */
class UpdateImportRecordsRenameModuleSlug extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_settings', function (Blueprint $table) {
            $table->renameColumn('module_handler', 'import_handler');
        });

        Schema::table('import_records', function (Blueprint $table) {
            $table->renameColumn('module_slug', 'import_handler');
        });

        \Mallto\Admin\Data\ImportSetting::query()
            ->chunk(50, function ($importSettings) {
                foreach ($importSettings as $importSetting) {
                    $importSetting->import_handler = trim($importSetting->import_handler, '\\');
                    $importSetting->save();
                }
            });

        \Mallto\Admin\Data\ImportRecord::query()
            ->chunk(50, function ($importRecords) {
                foreach ($importRecords as $importRecord) {
                    $importHandler = $importRecord->import_handler;

                    if ( ! str_contains($importHandler, "\\")) {
                        $importSetting = \Mallto\Admin\Data\ImportSetting::query()
                            ->where('module_slug', $importHandler)
                            ->first();
                        if ($importSetting) {
                            $importHandler = $importSetting->import_handler;
                        }
                    }
                    $importRecord->import_handler = trim($importHandler, '\\');
                    $importRecord->save();
                }
            });

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
