<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // daily, weekly, monthly, custom
            $table->date('start_date');
            $table->date('end_date');
            $table->json('data'); // Serialized report data
            $table->decimal('total_income', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Admin yang generate report
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};