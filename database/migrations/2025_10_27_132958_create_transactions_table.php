<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_create_transactions_table.php
public function up()
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('transaction_number')->unique();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->decimal('total_amount', 10, 2);
        $table->text('notes')->nullable();
        $table->enum('status', ['pending', 'processing', 'completed', 'picked_up'])->default('pending');
        $table->dateTime('order_date');
        $table->dateTime('estimated_completion')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
