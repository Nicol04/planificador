<?php

namespace App\Http\Controllers;

use App\Models\FichaAprendizaje;
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AjaxFichaAprendizajeController extends Controller
{
    // Listar todas las fichas con ejercicios
    public function index(): JsonResponse
    {
        $fichas = FichaAprendizaje::with('ejercicios')->get();
        return response()->json($fichas);
    }

    // Mostrar una ficha específica con ejercicios
    public function show($id): JsonResponse
    {
        $ficha = FichaAprendizaje::with('ejercicios')->findOrFail($id);
        return response()->json($ficha);
    }

    // Crear una nueva ficha y sus ejercicios
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'ejercicios' => 'array',
            'ejercicios.*.tipo' => 'nullable|string',
            'ejercicios.*.contenido' => 'nullable',
        ]);

        $ficha = FichaAprendizaje::create($data);

        if (!empty($data['ejercicios'])) {
            foreach ($data['ejercicios'] as $ejercicioData) {
                $ficha->ejercicios()->create($ejercicioData);
            }
        }

        return response()->json($ficha->load('ejercicios'), 201);
    }

    // Actualizar una ficha y sus ejercicios
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'ejercicios' => 'array',
            'ejercicios.*.id' => 'nullable|exists:ejercicios,id',
            'ejercicios.*.tipo' => 'nullable|string',
            'ejercicios.*.contenido' => 'nullable',
        ]);

        $ficha = FichaAprendizaje::findOrFail($id);
        $ficha->update($data);

        // Sincronizar ejercicios
        if (isset($data['ejercicios'])) {
            $ids = [];
            foreach ($data['ejercicios'] as $ejercicioData) {
                if (isset($ejercicioData['id'])) {
                    $ejercicio = Ejercicio::find($ejercicioData['id']);
                    if ($ejercicio) {
                        $ejercicio->update($ejercicioData);
                        $ids[] = $ejercicio->id;
                    }
                } else {
                    $nuevo = $ficha->ejercicios()->create($ejercicioData);
                    $ids[] = $nuevo->id;
                }
            }
            // Eliminar ejercicios que no están en la nueva lista
            $ficha->ejercicios()->whereNotIn('id', $ids)->delete();
        }

        return response()->json($ficha->load('ejercicios'));
    }

    // Eliminar una ficha y sus ejercicios
    public function destroy($id): JsonResponse
    {
        $ficha = FichaAprendizaje::findOrFail($id);
        $ficha->ejercicios()->delete();
        $ficha->delete();
        return response()->json(['message' => 'Ficha y ejercicios eliminados']);
    }
}
