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
        Schema::table('users', function (Blueprint $table) {
            $table->string('estado')->default('activo');
            $table->string('password_plano')->nullable();
            $table->unsignedBigInteger('persona_id')->nullable();

            // Clave foránea hacia la tabla personas
            $table->foreign('persona_id')
                ->references('id')
                ->on('personas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn(['estado', 'password_plano', 'persona_id']);
        });
    }
};
