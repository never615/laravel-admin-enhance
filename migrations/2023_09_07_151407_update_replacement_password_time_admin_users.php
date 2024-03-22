<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class UpdateReplacementPasswordTimeAdminUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->timestamp('replacement_password_time')->nullable()->comment('密码更换时间');
        });

        \Mallto\Admin\Data\AdminUser::query()
                ->chunkById(50, function ($adminUsers) {
                    foreach ($adminUsers as $adminUser) {
                        $adminUser->replacement_password_time = Carbon::now()->toDateTimeString();
                        $adminUser->save();
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
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('replacement_password_time');
        });
    }
}
