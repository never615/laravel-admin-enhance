<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubjectSettingsAddUnionPay extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subject_settings', function (Blueprint $table) {
            $table->dropColumn('union_pay_setting');
            $table->string('driver')->comment('支付网关');
            $table->string('cert_version')->comment('证书版本');
            $table->string('mer_id')->comment('商户号');
            $table->string('cert_dir')->comment('证书目录');
            $table->string('notify_url')->comment('支付后通知地址');
            $table->string('return_url')->comment('支付后回调地址');
            $table->string('cert_password')->comment('私钥密码');
            $table->string('enc_cert_path')->comment('敏感加密证书地址');
            $table->string('root_cert_path')->comment('根证书地址');
            $table->string('middle_cert_path')->comment('中级证书地址');
            $table->string('private_cert_path')->comment('私钥地址');
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
            $table->jsonb('union_pay_setting')->nullable()->comment('银联支付配置');
            $table->dropColumn('driver');
            $table->dropColumn('mer_id');
            $table->dropColumn('cert_version');
            $table->dropColumn('cert_dir');
            $table->dropColumn('notify_url');
            $table->dropColumn('return_url');
            $table->dropColumn('cert_password');
            $table->dropColumn('enc_cert_path');
            $table->dropColumn('root_cert_path');
            $table->dropColumn('middle_cert_path');
            $table->dropColumn('private_cert_path');
        });
    }
}
