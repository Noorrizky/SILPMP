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
    Schema::create('registrations', function (Blueprint $table) {
        $table->id();
        $table->string('registration_number')->unique(); // LAB-20240114-001
        $table->foreignId('patient_id')->constrained();
        $table->foreignId('user_id')->constrained(); // Petugas yg input
        $table->string('doctor_sender')->nullable();
        $table->enum('status', ['pending', 'processing', 'done'])->default('pending');
        $table->timestamps();
    });

    Schema::create('results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
        $table->foreignId('parameter_id')->constrained();
        // $table->string('result_value'); // Salah
        $table->string('result_value')->nullable();
        
        // SNAPSHOT Nilai Normal (PENTING! Agar data historis valid)
        $table->string('ref_range_snapshot')->nullable(); 
        
        $table->text('note')->nullable(); // Catatan per item
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_transactions');
    }
};
