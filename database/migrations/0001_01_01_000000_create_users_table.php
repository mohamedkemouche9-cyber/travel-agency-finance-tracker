<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: users - one row per account.
 * The bcrypt hash lives in password_hash; failed_attempts/locked_until
 * implement the login throttling; is_verified flips to 1 after the 6-digit
 * demo verification code has been entered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username', 20)->unique();
            $table->string('email', 120)->unique();
            $table->string('password_hash', 255);
            $table->string('currency', 8)->default('£');
            $table->boolean('is_verified')->default(false);
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->dateTime('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
