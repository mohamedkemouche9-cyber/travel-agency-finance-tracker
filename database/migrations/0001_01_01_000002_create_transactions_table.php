<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: transactions - the core financial records.
 * Money is DECIMAL(10,2) (never floats), the type is a strict ENUM and a
 * CHECK constraint keeps amounts positive at database level as well.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('transaction_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['income', 'expense']);
            $table->date('transaction_date');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date'], 'idx_tx_user_date');
            $table->index(['user_id', 'category_id'], 'idx_tx_user_cat');

            // Deleting an account removes its transactions
            $table->foreign('user_id', 'fk_transactions_user')
                ->references('user_id')->on('users')
                ->cascadeOnDelete();

            // A category that still has transactions cannot be deleted
            $table->foreign('category_id', 'fk_transactions_category')
                ->references('category_id')->on('categories')
                ->restrictOnDelete();
        });

        // Database-level safety net: amounts must be positive
        DB::statement('ALTER TABLE transactions
            ADD CONSTRAINT chk_tx_amount_positive CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
