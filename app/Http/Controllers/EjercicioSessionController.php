<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

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

        // Recuperar metadatos de la ficha (nombre y descripción) si existen
        $fichaNombre = Session::get('ficha_nombre', null);
        $fichaDescripcion = Session::get('ficha_descripcion', null);
        $grado = Session::get('grado', null);
        $fichaTipoEjercicio = Session::get('ficha_tipo_ejercicio', null);

        return response()->json([
            'success' => true,
            'data' => [
                'ejercicios' => $ejercicios,
                'count' => count($ejercicios),
                'ficha_nombre' => $fichaNombre,
                'ficha_descripcion' => $fichaDescripcion,
                'grado' => $grado,
                'ficha_tipo_ejercicio' => $fichaTipoEjercicio,
            ]
        ]);
    }

    /**
     * Almacenar un nuevo ejercicio en sesión
     */
    public function store(Request $request): JsonResponse
    {
        // Validación de datos recibidos
        $data = $request->validate([
            'tipo' => 'required|string|in:SelectionExercise,ClassificationExercise,ClozeExercise,ReflectionExercise',
            'contenido' => 'required|array',
            'descripcion' => 'sometimes|string',
            'nombre' => 'sometimes|string',
            'grado' => 'sometimes|string',
            'tipo_ejercicio' => 'sometimes|string'
        ]);

        // Log principal de entrada
        Log::info('[EjercicioSessionController@store] Datos recibidos:', $data);

        // Guardar descripción si existe
        if (isset($data['descripcion'])) {
            Session::put('ficha_descripcion', $data['descripcion']);
            Log::info('[EjercicioSessionController@store] Guardado: ficha_descripcion = ' . $data['descripcion']);
        }

        // Guardar nombre/título si existe
        if (isset($data['nombre'])) {
            Session::put('ficha_nombre', $data['nombre']);
            Log::info('[EjercicioSessionController@store] Guardado: ficha_nombre = ' . $data['nombre']);
        }

        // Guardar grado si existe
        if (isset($data['grado'])) {
            Session::put('grado', $data['grado']);
            Log::info('[EjercicioSessionController@store] Guardado: grado = ' . $data['grado']);
        }

        // Guardar tipo de ejercicio si existe
        if (isset($data['tipo_ejercicio'])) {
            Session::put('ficha_tipo_ejercicio', $data['tipo_ejercicio']);
            Log::info('[EjercicioSessionController@store] Guardado: ficha_tipo_ejercicio = ' . $data['tipo_ejercicio']);
        }

        // Obtener ejercicios actuales de la sesión
        $ejercicios = Session::get(self::SESSION_KEY, []);
        Log::info('[EjercicioSessionController@store] Ejercicios actuales:', $ejercicios);

        // Crear nuevo ejercicio
        $nuevoEjercicio = [
            'id' => uniqid('ej_'),
            'tipo' => $data['tipo'],
            'contenido' => $data['contenido'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Guardarlo en la sesión
        $ejercicios[] = $nuevoEjercicio;
        Session::put(self::SESSION_KEY, $ejercicios);
        Log::info('[EjercicioSessionController@store] Ejercicio agregado:', $nuevoEjercicio);

        // Respuesta final
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
        // Validamos lo mismo que en store, pero todo es opcional
        $data = $request->validate([
            'tipo' => 'sometimes|string|in:SelectionExercise,ClassificationExercise,ClozeExercise,ReflectionExercise',
            'contenido' => 'sometimes|array',
            'descripcion' => 'sometimes|string',
            'nombre' => 'sometimes|string',
            'grado' => 'sometimes|string',
            'tipo_ejercicio' => 'sometimes|string'
        ]);

        Log::info('[EjercicioSessionController@update] Datos recibidos:', $data);

        // 🔥 GUARDAR METADATOS TAMBIÉN AL EDITAR 🔥
        if (isset($data['descripcion'])) {
            Session::put('ficha_descripcion', $data['descripcion']);
            Log::info('[update] ficha_descripcion actualizada');
        }

        if (isset($data['nombre'])) {
            Session::put('ficha_nombre', $data['nombre']);
            Log::info('[update] ficha_nombre actualizada');
        }

        if (isset($data['grado'])) {
            Session::put('grado', $data['grado']);
            Log::info('[update] grado actualizado');
        }

        if (isset($data['tipo_ejercicio'])) {
            Session::put('ficha_tipo_ejercicio', $data['tipo_ejercicio']);
            Log::info('[update] ficha_tipo_ejercicio actualizado');
        }

        // Actualizamos el ejercicio
        $ejercicios = Session::get(self::SESSION_KEY, []);

        $index = collect($ejercicios)->search(fn($ej) => $ej['id'] === $id);

        if ($index === false) {
            return response()->json([
                'success' => false,
                'message' => 'Ejercicio no encontrado en sesión'
            ], 404);
        }

        // Actualización del ejercicio
        $ejercicios[$index] = array_merge($ejercicios[$index], [
            'tipo' => $data['tipo'] ?? $ejercicios[$index]['tipo'],
            'contenido' => $data['contenido'] ?? $ejercicios[$index]['contenido'],
            'updated_at' => now()->toISOString(),
        ]);

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
        // Eliminar ejercicios y metadatos relacionados con la ficha
        Session::forget(self::SESSION_KEY);
        Session::forget('ficha_nombre');
        Session::forget('ficha_descripcion');
        // También limpiar la clave usada por el modelo
        Session::forget('grado');
        Session::forget('ficha_tipo_ejercicio');

        return response()->json([
            'success' => true,
            'message' => 'Todos los ejercicios y metadatos de la ficha eliminados de sesión'
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
            // Opcionales: nombre y descripción de la ficha para almacenar en sesión
            'ficha_nombre' => 'sometimes|string',
            'ficha_descripcion' => 'sometimes|string',
            'grado' => 'sometimes|string',
            'tipo_ejercicio' => 'sometimes|string',
        ]);

        // Log de los datos recibidos
        Log::info('[EjercicioSessionController@replaceAll] Datos recibidos:', $data);

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
        Log::info('[EjercicioSessionController@replaceAll] Ejercicios guardados en sesión:', $ejercicios);

        // Si se envió nombre/descripcion de la ficha, guardarlos en sesión
        if (isset($data['ficha_nombre'])) {
            Session::put('ficha_nombre', $data['ficha_nombre']); // También en ficha_nombre para consistencia
            Log::info('[EjercicioSessionController@replaceAll] Guardando en sesión: ficha_nombre = ' . $data['ficha_nombre']);
        }

        if (isset($data['ficha_descripcion'])) {
            Session::put('ficha_descripcion', $data['ficha_descripcion']);
            Log::info('[EjercicioSessionController@replaceAll] Guardando en sesión: ficha_descripcion = ' . $data['ficha_descripcion']);
        }

        // Si se envió grado de la ficha, guardarlo en sesión
        if (isset($data['grado'])) {
            Session::put('grado', $data['grado']);
            Log::info('[EjercicioSessionController@replaceAll] Guardando en sesión: grado = ' . $data['grado']);
        }

        // Si se envió tipo_ejercicio de la ficha, guardarlo en sesión
        if (isset($data['tipo_ejercicio'])) {
            Session::put('ficha_tipo_ejercicio', $data['tipo_ejercicio']);
            Log::info('[EjercicioSessionController@replaceAll] Guardando en sesión: ficha_tipo_ejercicio = ' . $data['tipo_ejercicio']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ejercicios reemplazados en sesión',
            'data' => [
                'ejercicios' => $ejercicios,
                'count' => count($ejercicios),
                'ficha_nombre' => Session::get('ficha_nombre', null),
                'ficha_descripcion' => Session::get('ficha_descripcion', null),
                'grado' => Session::get('grado', null),
                'ficha_tipo_ejercicio' => Session::get('ficha_tipo_ejercicio', null),
            ]
        ]);
    }

    /**
     * Actualizar solo los metadatos de la ficha (nombre, descripcion, grado, tipo_ejercicio)
     * sin afectar los ejercicios existentes
     */
    public function updateMetadata(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'sometimes|string',
            'grado' => 'sometimes|string',
            'tipo_ejercicio' => 'sometimes|string'
        ]);

        Log::info('[EjercicioSessionController@updateMetadata] Actualizando metadatos:', $data);

        // Actualizar cada metadato en sesión si existe
        if (isset($data['nombre'])) {
            Session::put('ficha_nombre', $data['nombre']);
            Log::info('[updateMetadata] ficha_nombre actualizado: ' . $data['nombre']);
        }

        if (isset($data['descripcion'])) {
            Session::put('ficha_descripcion', $data['descripcion']);
            Log::info('[updateMetadata] ficha_descripcion actualizada');
        }

        if (isset($data['grado'])) {
            Session::put('grado', $data['grado']);
            Log::info('[updateMetadata] grado actualizado: ' . $data['grado']);
        }

        if (isset($data['tipo_ejercicio'])) {
            Session::put('ficha_tipo_ejercicio', $data['tipo_ejercicio']);
            Log::info('[updateMetadata] ficha_tipo_ejercicio actualizado: ' . $data['tipo_ejercicio']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Metadatos actualizados en sesión',
            'data' => [
                'ficha_nombre' => Session::get('ficha_nombre'),
                'ficha_descripcion' => Session::get('ficha_descripcion'),
                'grado' => Session::get('grado'),
                'ficha_tipo_ejercicio' => Session::get('ficha_tipo_ejercicio'),
            ]
        ]);
    }
}