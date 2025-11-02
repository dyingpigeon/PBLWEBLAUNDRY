<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // 2025_10_27_132958_create_transactions_table.php
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('order_type', ['kiloan', 'satuan'])->default('kiloan'); // TAMBAH: jenis order
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('weight', 8, 2)->nullable(); // TAMBAH: untuk kiloan
            $table->enum('payment_type', ['now', 'later'])->default('later'); // TAMBAH: bayar sekarang/nanti
            $table->enum('status', ['new', 'process', 'ready', 'done', 'cancelled'])->default('new');
            $table->enum('payment_status', ['pending', 'paid', 'partial'])->default('pending');
            $table->enum('payment_method', ['cash', 'transfer', 'qris'])->nullable(); // UBAH: nullable
            $table->text('notes')->nullable();
            $table->dateTime('order_date');
            $table->dateTime('estimated_completion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};