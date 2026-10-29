<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: categories.
 *   user_id NULL  -> global default category available to every account
 *   user_id set   -> personal category owned (and manageable) by that user
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('name', 40);
            $table->char('color', 7)->default('#6366f1');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'name'], 'uq_categories_owner_name');

            // Deleting an account removes the user's personal categories
            $table->foreign('user_id', 'fk_categories_user')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
