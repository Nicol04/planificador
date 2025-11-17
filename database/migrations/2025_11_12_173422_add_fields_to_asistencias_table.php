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
            if (!Schema::hasColumn('asistencias', 'id')) {
                $table->id()->first();
            }
            // id de plantilla usada
            $table->foreignId('plantilla_id')->nullable()->constrained('plantillas')->after('docente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            if (Schema::hasColumn('asistencias', 'plantilla_id')) {
                $table->dropForeign(['plantilla_id']);
                $table->dropColumn('plantilla_id');
            }
            if (Schema::hasColumn('asistencias', 'id')) {
                $table->dropColumn('id');
            }
        });
    }
};
