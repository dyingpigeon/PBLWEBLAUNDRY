<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->onDelete('cascade');
            $table->string('name'); // Baju, Celana, Jaket, Jas, Sepatu, Boneka, dll
            $table->decimal('price', 12, 2);
            $table->string('unit')->default('kg'); // kg, pcs, set, dll
            $table->integer('estimation_time')->default(24); // estimasi jam
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // untuk variasi (warna, ukuran, dll)
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};