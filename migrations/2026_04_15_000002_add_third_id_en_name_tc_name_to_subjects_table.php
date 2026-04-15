<?php

/**
 * Copyright (c) 2026. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 添加主体的第三方ID、英文名称、繁体名称字段
 */
class AddThirdIdEnNameTcNameToSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('third_code')->nullable()->after('third_id')->comment('第三方ID');
            $table->string('en_name')->nullable()->after('third_id')->comment('英文名称');
            $table->string('tc_name')->nullable()->after('en_name')->comment('繁体名称');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['third_id', 'en_name', 'tc_name']);
        });
    }
}
