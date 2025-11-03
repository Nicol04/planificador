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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->foreignId('docente_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_aula')->nullable(); // Ej. "5°A", "4°B"
            $table->string('mes')->nullable(); // Ej. "Marzo", "Abril"
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Lista de estudiantes como JSON
            // Ejemplo: ["María López", "Carlos Ramos", "Andrea Vega"]
            $table->json('estudiantes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
