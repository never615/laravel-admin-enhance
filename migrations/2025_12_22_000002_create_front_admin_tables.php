<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('front_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->string('slug')->index();
            $table->text('describe')->nullable();
            $table->timestamps();
            $table->unique(['subject_id', 'slug']);
        });

        Schema::create('front_admin_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('username');
            $table->string('password');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('status')->default('normal');
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['subject_id', 'username']);
        });

        Schema::create('front_role_admin_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('admin_user_id');
            $table->primary(['role_id', 'admin_user_id']);
        });

        Schema::create('front_role_api_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedInteger('permission_id');
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_role_api_permissions');
        Schema::dropIfExists('front_role_admin_user');
        Schema::dropIfExists('front_admin_users');
        Schema::dropIfExists('front_roles');
    }
};

