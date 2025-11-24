<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FichaAprendizaje extends Model
{
    use HasFactory;

    protected $table = 'fichas_aprendizaje';

    protected $fillable = [
        'nombre',
        'descripcion',
        'user_id',
        'grado',
        'tipo_ejercicio',
        'public',
    ];

    protected static function boot()
    {
        parent::boot();

        /**
         * ASIGNAR valores desde sesión antes de crear o actualizar
         */
        static::saving(function ($ficha) {

            // Nombre (titulo)
            if ($titulo = Session::get('ficha_nombre')) {
                $ficha->nombre = $titulo;
            }

            // Descripción
            if ($desc = Session::get('ficha_descripcion')) {
                $ficha->descripcion = $desc;
            }

            // Grado
            if ($grado = Session::get('grado')) {
                $ficha->grado = $grado;
            }

            // Tipo de ejercicio general
            if ($tipoFicha = Session::get('ficha_tipo_ejercicio')) {
                $ficha->tipo_ejercicio = $tipoFicha;
            }

                // Asociar usuario logueado
                if (Auth::check()) {
                    $ficha->user_id = Auth::id();
                }
        });

        /**
         * DESPUÉS de guardar: asociar ejercicios
         */
        static::saved(function ($ficha) {
            // Asociar ejercicios
            $ejercicios = Session::get('ejercicios_ficha_aprendizaje', []);
            if (!empty($ejercicios)) {
                Log::info("📦 Procesando " . count($ejercicios) . " ejercicios para Ficha #{$ficha->id}");
                // Eliminar ejercicios anteriores si era actualización
                if (!$ficha->wasRecentlyCreated) {
                    $ficha->ejercicios()->delete();
                    Log::info("🗑️ Ejercicios anteriores eliminados");
                }
                // Crear los ejercicios
                foreach ($ejercicios as $e) {
                    try {
                        $ficha->ejercicios()->create([
                            'tipo' => $e['tipo'],
                            'contenido' => $e['contenido'],
                        ]);
                    } catch (\Exception $err) {
                        Log::error("❌ Error creando ejercicio: " . $err->getMessage());
                    }
                }
                Log::info("✓ Ejercicios asociados correctamente");
            }

            // Asociar FichaSesion si existe sesion_id en la sesión o en el modelo
            $sesionId = $ficha->sesion_id ?? Session::get('sesion_id');
            if ($sesionId) {
                try {
                    \App\Models\FichaSesion::create([
                        'ficha_aprendizaje_id' => $ficha->id,
                        'sesion_id' => $sesionId,
                    ]);
                    Log::info("✓ FichaSesion asociada correctamente a FichaAprendizaje #{$ficha->id} y Sesion #{$sesionId}");
                } catch (\Exception $err) {
                    Log::error("❌ Error asociando FichaSesion: " . $err->getMessage());
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ejercicios()
    {
        return $this->hasMany(Ejercicio::class);
    }

    public function fichaSesiones()
    {
        return $this->hasMany(FichaSesion::class);
    }

    /**
     * LIMPIAR sesiones después de crear o actualizar
     */
    public static function limpiarSesionEjercicios()
    {
        Session::forget('ejercicios_ficha_aprendizaje');
        Session::forget('ficha_descripcion');
        Session::forget('ficha_nombre');
        Session::forget('grado');
        Session::forget('ficha_tipo_ejercicio');

        Log::info("🧹 Sesión de ficha limpiada correctamente.");
    }
}