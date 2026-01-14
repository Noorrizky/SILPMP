<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: Hematologi
        $table->timestamps();
    });

    Schema::create('parameters', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->string('name'); // Contoh: Hemoglobin
        $table->string('unit')->nullable(); // Contoh: g/dL
        // Simpan range sebagai string text agar fleksibel
        $table->string('ref_range_male')->nullable(); 
        $table->string('ref_range_female')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_parameters');
    }
};
