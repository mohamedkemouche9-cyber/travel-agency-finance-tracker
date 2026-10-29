<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: user_codes - 6-digit codes for account verification and password
 * reset. Only the SHA-256 hash of a code is stored, never the code itself.
 * Delivery is demo-only: the plain code is flashed on screen for a few
 * seconds instead of being e-mailed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_codes', function (Blueprint $table) {
            $table->increments('code_id');
            $table->unsignedInteger('user_id');
            $table->enum('purpose', ['verify', 'reset']);
            $table->char('code_hash', 64);
            $table->dateTime('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'purpose', 'used_at'], 'idx_codes_user_purpose');

            $table->foreign('user_id', 'fk_codes_user')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_codes');
    }
};
