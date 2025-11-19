<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

/**
 * Controlador para gestionar ejercicios en variables de sesión de Laravel.
 * NO persiste datos en base de datos, solo en sesión.
 */
class EjercicioSessionController extends Controller
{
    // Clave para almacenar ejercicios en sesión
    const SESSION_KEY = 'ejercicios_ficha_aprendizaje';

    /**
     * Obtener todos los ejercicios almacenados en sesión
     */
    public function index(): JsonResponse
    {
        $ejercicios = Session::get(self::SESSION_KEY, []);
        
        return response()->json([
            'success' => true,
            'data' => $ejercicios,
            'count' => count($ejercicios)
        ]);
    }

    /**
     * Almacenar un nuevo ejercicio en sesión
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipo' => 'required|string|in:SelectionExercise,ClassificationExercise,ClozeExercise,ReflectionExercise',
            'contenido' => 'required|array',
            'descripcion_ficha' => 'sometimes|string', // Descripción opcional de la ficha
        ]);

        // Si viene descripción de la ficha, guardarla en sesión
        if (isset($data['descripcion_ficha'])) {
            Session::put('ficha_contenido_descripcion', $data['descripcion_ficha']);
        }

        // Obtener ejercicios actuales
        $ejercicios = Session::get(self::SESSION_KEY, []);

        // Crear nuevo ejercicio con ID único basado en timestamp
        $nuevoEjercicio = [
            'id' => uniqid('ej_'),
            'tipo' => $data['tipo'],
            'contenido' => $data['contenido'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Agregar al array
        $ejercicios[] = $nuevoEjercicio;

        // Guardar en sesión
        Session::put(self::SESSION_KEY, $ejercicios);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio almacenado en sesión',
            'data' => $nuevoEjercicio
        ], 201);
    }

    /**
     * Obtener un ejercicio específico por ID
     */
    public function show(string $id): JsonResponse
    {
        $ejercicios = Session::get(self::SESSION_KEY, []);
        
        $ejercicio = collect($ejercicios)->firstWhere('id', $id);

        if (!$ejercicio) {
            return response()->json([
                'success' => false,
                'message' => 'Ejercicio no encontrado en sesión'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ejercicio
        ]);
    }

    /**
     * Actualizar un ejercicio existente en sesión
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'tipo' => 'sometimes|string|in:SelectionExercise,ClassificationExercise,ClozeExercise,ReflectionExercise',
            'contenido' => 'sometimes|array',
        ]);

        $ejercicios = Session::get(self::SESSION_KEY, []);
        
        // Buscar índice del ejercicio
        $index = collect($ejercicios)->search(fn($ej) => $ej['id'] === $id);

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'Ejercicio no encontrado en sesión'
            ], 404);
        }

        // Actualizar ejercicio
        $ejercicios[$index] = array_merge($ejercicios[$index], [
            'tipo' => $data['tipo'] ?? $ejercicios[$index]['tipo'],
            'contenido' => $data['contenido'] ?? $ejercicios[$index]['contenido'],
            'updated_at' => now()->toISOString(),
        ]);

        // Guardar en sesión
        Session::put(self::SESSION_KEY, $ejercicios);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio actualizado en sesión',
            'data' => $ejercicios[$index]
        ]);
    }

    /**
     * Actualizar contenido parcial de un ejercicio (útil para cambios de imagen o texto)
     */
    public function updateContent(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'path' => 'required|string', // Ejemplo: "title", "options.0.imageSrc", "items.2.text"
            'value' => 'required',
        ]);

        $ejercicios = Session::get(self::SESSION_KEY, []);
        
        $index = collect($ejercicios)->search(fn($ej) => $ej['id'] === $id);

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'Ejercicio no encontrado en sesión'
            ], 404);
        }

        // Actualizar contenido usando notación de puntos
        $contenido = $ejercicios[$index]['contenido'];
        data_set($contenido, $data['path'], $data['value']);
        
        $ejercicios[$index]['contenido'] = $contenido;
        $ejercicios[$index]['updated_at'] = now()->toISOString();

        Session::put(self::SESSION_KEY, $ejercicios);

        return response()->json([
            'success' => true,
            'message' => 'Contenido actualizado',
            'data' => $ejercicios[$index]
        ]);
    }

    /**
     * Eliminar un ejercicio de sesión
     */
    public function destroy(string $id): JsonResponse
    {
        $ejercicios = Session::get(self::SESSION_KEY, []);
        
        $index = collect($ejercicios)->search(fn($ej) => $ej['id'] === $id);

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'Ejercicio no encontrado en sesión'
            ], 404);
        }

        // Eliminar ejercicio
        unset($ejercicios[$index]);
        $ejercicios = array_values($ejercicios); // Reindexar array

        Session::put(self::SESSION_KEY, $ejercicios);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicio eliminado de sesión'
        ]);
    }

    /**
     * Limpiar todos los ejercicios de sesión
     */
    public function clear(): JsonResponse
    {
        Session::forget(self::SESSION_KEY);

        return response()->json([
            'success' => true,
            'message' => 'Todos los ejercicios eliminados de sesión'
        ]);
    }

    /**
     * Reemplazar todos los ejercicios en sesión (útil al cargar una ficha completa)
     */
    public function replaceAll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ejercicios' => 'required|array',
            'ejercicios.*.tipo' => 'required|string|in:SelectionExercise,ClassificationExercise,ClozeExercise,ReflectionExercise',
            'ejercicios.*.contenido' => 'required|array',
        ]);

        // Agregar IDs y timestamps a cada ejercicio
        $ejercicios = collect($data['ejercicios'])->map(function ($ejercicio) {
            return [
                'id' => uniqid('ej_'),
                'tipo' => $ejercicio['tipo'],
                'contenido' => $ejercicio['contenido'],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];
        })->toArray();

        Session::put(self::SESSION_KEY, $ejercicios);

        return response()->json([
            'success' => true,
            'message' => 'Ejercicios reemplazados en sesión',
            'data' => $ejercicios,
            'count' => count($ejercicios)
        ]);
    }
}