<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SesionMomentoSessionController extends Controller
{
    /**
     * Store the given values in session variables.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inicio' => 'required|string',
            'desarrollo' => 'required|string',
            'cierre' => 'required|string',
        ]);

        Session::put('sesion_momento_inicio', $validated['inicio']);
        Session::put('sesion_momento_desarrollo', $validated['desarrollo']);
        Session::put('sesion_momento_cierre', $validated['cierre']);

        return response()->json(['message' => 'Valores guardados en la sesión.']);
    }
    /**
     * Devuelve el momento de sesión según el id recibido por parámetro.
     */
    public function showById($sesionId)
    {
        $momentos = \App\Models\SesionMomento::findAllBySesionId($sesionId);
        if (empty($momentos)) {
            return response()->json(['error' => 'No se encontraron momentos para la sesión dada.'], 404);
        }
        return response()->json(['momentos' => $momentos]);
    }
}
