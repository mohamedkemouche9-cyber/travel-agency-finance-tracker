<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: budgets - monthly spending limit per category.
 * One row per user + category + month; the month is stored as its first day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->increments('budget_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->date('month_start');
            $table->decimal('amount', 10, 2);

            $table->unique(['user_id', 'category_id', 'month_start'], 'uq_budget');

            $table->foreign('user_id', 'fk_budgets_user')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('category_id', 'fk_budgets_category')
                ->references('category_id')->on('categories')
                ->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE budgets
            ADD CONSTRAINT chk_budget_amount CHECK (amount >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
