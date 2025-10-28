<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->enum('status', ['new', 'washing', 'ironing', 'ready', 'picked_up', 'cancelled'])->default('new');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'overpaid'])->default('pending');
            $table->enum('payment_method', ['cash', 'transfer', 'qris'])->default('cash');
            $table->json('timeline')->nullable();
            $table->dateTime('order_date');
            $table->dateTime('estimated_completion')->nullable();
            $table->dateTime('washing_started_at')->nullable();
            $table->dateTime('ironing_started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('picked_up_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};