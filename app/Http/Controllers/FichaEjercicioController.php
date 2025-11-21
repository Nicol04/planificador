<?php

namespace App\Http\Controllers;

use App\Models\FichaAprendizaje;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para obtener ejercicios asociados a una FichaAprendizaje
 */
class FichaEjercicioController extends Controller
{
    /**
     * Obtener todos los ejercicios de una ficha específica
     * @param int $fichaId - ID de la FichaAprendizaje
     */
    public function getEjercicios(int $fichaId): JsonResponse
    {
        try {
            $ficha = FichaAprendizaje::with('ejercicios')->findOrFail($fichaId);

            $ejercicios = $ficha->ejercicios->map(function ($ejercicio) {
                return [
                    'id' => $ejercicio->id,
                    'tipo' => $ejercicio->tipo,
                    'contenido' => $ejercicio->contenido,
                    'created_at' => $ejercicio->created_at->toISOString(),
                    'updated_at' => $ejercicio->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'ficha_id' => $ficha->id,
                    'nombre' => $ficha->nombre,
                    'descripcion' => $ficha->descripcion ?? '',
                    'grado' => $ficha->grado, // aqui retorna el grado del docente
                    'tipo_ejercicio' => $ficha->tipo_ejercicio, 
                    'ejercicios' => $ejercicios,
                    'count' => $ejercicios->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ejercicios: ' . $e->getMessage()
            ], 404);
        }
    }
}
