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
        Schema::create('plantillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null'); // Si se elimina el usuario, no borra la plantilla
            $table->string('nombre');
            $table->string('tipo'); // Por ejemplo: 'asistencia', 'examen', 'informe'
            $table->string('archivo'); // Ruta del archivo Word/PDF
            $table->string('imagen_preview')->nullable(); // Vista previa opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas');
    }
};
