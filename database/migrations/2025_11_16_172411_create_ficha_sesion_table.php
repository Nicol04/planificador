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
        Schema::create('ficha_sesion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_aprendizaje_id')->constrained('fichas_aprendizaje')->onDelete('cascade');
            $table->foreignId('sesion_id')->constrained('sesions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ficha_sesion');
    }
};
