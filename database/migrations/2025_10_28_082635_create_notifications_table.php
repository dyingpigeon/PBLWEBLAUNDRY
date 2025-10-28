<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // new_order, status_update, reminder, system
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like order_id, customer_name, etc
            $table->boolean('read')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Admin yang menerima notifikasi
            $table->foreignId('related_id')->nullable(); // ID terkait (order_id, customer_id, etc)
            $table->string('related_type')->nullable(); // Model terkait (Order, Customer, etc)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};