<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class FichaAprendizaje extends Model
{
    use HasFactory;
    protected $table = 'fichas_aprendizaje';


    protected $fillable = [
        'nombre',
        'descripcion',
        'user_id',
    ];

    /**
     * Boot del modelo para auto-asociar ejercicios desde sesión
     */
    protected static function boot()
    {
        parent::boot();

        // Evento: ANTES de crear, generar nombre automático si está vacío
        static::creating(function ($ficha) {
            if (empty($ficha->nombre)) {
                $ficha->nombre = 'Ficha ' . now()->format('d/m/Y H:i');
            }

            // Obtener descripción desde sesión si está disponible
            $contenidoSesion = Session::get('ficha_contenido_descripcion');
            if (!empty($contenidoSesion) && empty($ficha->descripcion)) {
                $ficha->descripcion = $contenidoSesion;
            }
        });

        // Evento: ANTES de actualizar, obtener descripción desde sesión si está disponible
        static::updating(function ($ficha) {
            $contenidoSesion = Session::get('ficha_contenido_descripcion');
            if (!empty($contenidoSesion)) {
                $ficha->descripcion = $contenidoSesion;
            }
        });

        // Evento: después de GUARDAR una FichaAprendizaje (incluye create y update)
        static::saved(function ($ficha) {
            // Obtener ejercicios almacenados en sesión
            $ejerciciosSesion = Session::get('ejercicios_ficha_aprendizaje', []);

            if (!empty($ejerciciosSesion)) {
                Log::info("📦 Procesando " . count($ejerciciosSesion) . " ejercicios para FichaAprendizaje #{$ficha->id}");
                
                // Si fue recién creado, crear nuevos ejercicios
                if ($ficha->wasRecentlyCreated) {
                    foreach ($ejerciciosSesion as $ejercicioData) {
                        try {
                            $ficha->ejercicios()->create([
                                'tipo' => $ejercicioData['tipo'],
                                'contenido' => $ejercicioData['contenido'],
                            ]);
                        } catch (\Exception $e) {
                            Log::error("❌ Error creando ejercicio {$ejercicioData['tipo']}: " . $e->getMessage());
                        }
                    }
                    Log::info("✓ FichaAprendizaje #{$ficha->id}: " . count($ejerciciosSesion) . " ejercicios creados desde sesión");
                } else {
                    // Si es una actualización, eliminar ejercicios antiguos y crear los nuevos
                    $ficha->ejercicios()->delete();
                    Log::info("🗑️ Ejercicios antiguos eliminados");
                    
                    foreach ($ejerciciosSesion as $ejercicioData) {
                        try {
                            $ficha->ejercicios()->create([
                                'tipo' => $ejercicioData['tipo'],
                                'contenido' => $ejercicioData['contenido'],
                            ]);
                        } catch (\Exception $e) {
                            Log::error("❌ Error actualizando ejercicio {$ejercicioData['tipo']}: " . $e->getMessage());
                        }
                    }
                    Log::info("✓ FichaAprendizaje #{$ficha->id}: " . count($ejerciciosSesion) . " ejercicios actualizados desde sesión");
                }
            }

            // NOTA: NO limpiamos las variables de sesión aquí
            // Deben limpiarse manualmente por el usuario o al navegar a otra página
        });
    }    public function user()
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
     * Limpiar variables de sesión relacionadas con ejercicios y descripción
     * Debe llamarse manualmente después de guardar la ficha
     */
    public static function limpiarSesionEjercicios()
    {
        Session::forget('ejercicios_ficha_aprendizaje');
        Session::forget('ficha_contenido_descripcion');
        Log::info("🧹 Variables de sesión limpiadas (ejercicios + descripción)");
    }
}