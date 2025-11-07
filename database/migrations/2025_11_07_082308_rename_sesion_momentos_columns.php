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
        Schema::table('sesion_momentos', function (Blueprint $table) {
            if (Schema::hasColumn('sesion_momentos', 'nombre_momento')) {
                $table->renameColumn('nombre_momento', 'inicio');
            }
            if (Schema::hasColumn('sesion_momentos', 'descripcion')) {
                $table->renameColumn('descripcion', 'desarrollo');
            }
            if (Schema::hasColumn('sesion_momentos', 'actividades')) {
                $table->renameColumn('actividades', 'cierre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesion_momentos', function (Blueprint $table) {
            if (Schema::hasColumn('sesion_momentos', 'inicio')) {
                $table->renameColumn('inicio', 'nombre_momento');
            }
            if (Schema::hasColumn('sesion_momentos', 'desarrollo')) {
                $table->renameColumn('desarrollo', 'descripcion');
            }
            if (Schema::hasColumn('sesion_momentos', 'cierre')) {
                $table->renameColumn('cierre', 'actividades');
            }
        });
    }
};
