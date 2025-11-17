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
        Schema::table('asistencias', function (Blueprint $table) {
            if (Schema::hasColumn('asistencias', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
            if (Schema::hasColumn('asistencias', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }

            // Agregar nuevas columnas
            $table->integer('anio')->after('mes');

            // Guardar días sin clase como JSON
            $table->json('dias_no_clase')->nullable()->after('anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Eliminar columnas nuevas
            $table->dropColumn('anio');
            $table->dropColumn('dias_no_clase');
        });
    }
};
