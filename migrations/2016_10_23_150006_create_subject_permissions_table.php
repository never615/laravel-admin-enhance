<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectPermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_permissions', function (Blueprint $table) {

            $table->increments("id");

            $table->integer('subject_id')->comment('主体id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('CASCADE');

            $table->unsignedInteger("permission_id");
            $table->foreign('permission_id')->references('id')->on('admin_permissions')->onDelete('CASCADE');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subject_permissions');
    }
}
