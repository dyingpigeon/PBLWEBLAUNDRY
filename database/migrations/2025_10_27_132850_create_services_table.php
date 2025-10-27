<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_create_services_table.php
public function up()
{
    Schema::create('services', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('category')->default('general');
        $table->text('description')->nullable();
        $table->string('icon')->default('fas fa-tshirt');
        $table->string('color')->default('blue-500');
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
