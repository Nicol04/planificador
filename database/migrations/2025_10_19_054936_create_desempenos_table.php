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
        Schema::create('desempenos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacidad_id')->nullable()->constrained('capacidades')->onDelete('cascade');
            $table->foreignId('capacidad_transversal_id')->nullable()->constrained('capacidades_transversales')->onDelete('cascade');
            $table->foreignId('estandar_id')->nullable()->constrained('estandares')->onDelete('cascade');
            $table->string('grado')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desempenos');
    }
};
