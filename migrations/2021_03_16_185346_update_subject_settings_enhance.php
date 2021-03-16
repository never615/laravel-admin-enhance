<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubjectSettingsEnhance extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subject_settings', function (Blueprint $table) {
            $table->jsonb('public_configs')->nullable()->comment('公开配置,可以通过接口请求到');
            $table->jsonb('private_configs')->nullable()->comment('私有配置');
            $table->jsonb('subject_owner_configs')->nullable()->comment('主体拥有者可以配置的');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subject_settings', function (Blueprint $table) {
            $table->dropColumn('public_configs');
            $table->dropColumn('private_configs');
            $table->dropColumn('subject_owner_configs');
        });
    }
}
