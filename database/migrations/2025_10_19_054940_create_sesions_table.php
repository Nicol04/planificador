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
        Schema::create('sesions', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->string('dia')->nullable();
            $table->string('titulo');
            $table->string('tema')->nullable();
            $table->string('tiempo_estimado')->nullable();
            $table->text('proposito_sesion')->nullable();
            $table->foreignId('aula_curso_id')->nullable()->constrained('aula_curso')->onDelete('cascade');
            $table->foreignId('docente_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesions');
    }
};
