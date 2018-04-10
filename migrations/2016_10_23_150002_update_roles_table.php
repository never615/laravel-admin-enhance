<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('admin.database.roles_table'), function ($table) {
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('CASCADE');

            $table->text("describe")->nullable();

            //索引
            $table->index(['subject_id']);
            $table->unique(["subject_id","slug"]);
            $table->unique(["subject_id","name"]);
        });


        $connection = config('admin.database.connection') ?: config('database.default');

        Schema::connection($connection)->table(config('admin.database.users_table'), function (Blueprint $table) {
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('CASCADE');
            $table->unsignedInteger('adminable_id')->nullable();
            $table->string('adminable_type')->nullable()->comment('账户类型.subject:主体账户;shop:店铺账户');
            $table->index(['subject_id']);
            $table->unique(["subject_id", "username"]);
        });

        Schema::connection($connection)->table(config('admin.database.permissions_table'),
            function (Blueprint $table) {
                $table->text("describe")->nullable();
                $table->boolean("common")->default(false)->comment("是否是所有主体都拥有的权限,必须设置到权限组上");
            });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('admin.database.roles_table'), function ($table) {
            $table->dropColumn('subject_id');
            $table->dropColumn('describe');
            $table->dropIndex(['subject_id']);
            $table->dropUnique(["subject_id","slug"]);
            $table->dropUnique(["subject_id","name"]);
        });


        $connection = config('admin.database.connection') ?: config('database.default');
        Schema::connection($connection)->table(config('admin.database.users_table'), function (Blueprint $table) {
            $table->dropColumn('subject_id');
            $table->dropColumn('adminable_id');
            $table->dropColumn('adminable_type');
        });

        Schema::connection($connection)->table(config('admin.database.permissions_table'),
            function (Blueprint $table) {
                $table->dropColumn("describe");
                $table->dropColumn("common");
            });
    }
}
