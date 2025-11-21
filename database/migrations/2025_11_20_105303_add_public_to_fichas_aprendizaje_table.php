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
        Schema::table('fichas_aprendizaje', function (Blueprint $table) {
            $table->boolean('public')->default(false)->after('tipo_ejercicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fichas_aprendizaje', function (Blueprint $table) {
            $table->dropColumn(['public']);
        });
    }
};
