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
        Schema::create('sesion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained()->onDelete('cascade');
            $table->json('propositos_aprendizaje')->nullable();
            $table->json('transversalidad')->nullable();
            $table->text('evidencia')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesion_detalles');
    }
};
