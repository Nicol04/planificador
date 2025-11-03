<?php

namespace App\Http\Controllers\Documents;

use App\Models\Año;
use App\Models\Estudiante;
use App\Models\usuario_aula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;

class AsistenciaDocumentController extends DocumentController
{
    // Descargar / generar docx
    public function previsualizar($id, Request $request)
    {
        
    }
    public function vistaPreviaHtml(Request $request)
    {
        $mes = $request->query('mes', now()->translatedFormat('F'));

        // Determinar el aula actual del docente autenticado
        $año = Año::whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->first();

        $ua = usuario_aula::where('user_id', Auth::id())
            ->when($año, fn($q) => $q->where('año_id', $año->id)) 
            ->first();

        // Obtener lista de estudiantes del aula
        $estudiantes = [];
        if ($ua?->aula_id) {
            $estudiantes = Estudiante::where('aula_id', $ua->aula_id)
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'nombre' => trim(($e->apellidos ?? '') . ' ' . ($e->nombres ?? '')),
                ]);
        }

        // Enviar datos a la plantilla Blade
        return view('Docs.templates.asistencias.1H', [
            'orientacion' => 'horizontal',
            'mes' => $mes,
            'estudiantes' => $estudiantes,
        ]);
    }
    private function generarDocumento($lista_cotejo, $datosGenerales, $orientacion)
    {
        
    }

    private function procesarVariablesGenerales($templateProcessor, $datosGenerales)
    {
        
    }
}
