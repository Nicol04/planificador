<?php

namespace App\Http\Controllers\Documents;

use App\Models\Sesion;
use App\Models\User;
use App\Models\AulaCurso;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\ListaCotejo;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class ListasCotejoDocumentController extends DocumentController
{
    // Descargar / generar docx
    public function previsualizar($id, Request $request)
    {
        $lista = ListaCotejo::findOrFail($id);
        $orientacion = $request->get('orientacion', 'vertical');

        $competencia = $lista->competencia_id ? Competencia::find($lista->competencia_id)?->nombre : '';
        $titulo = $lista->titulo ?? '';
        $criteriosText = $lista->descripcion ?? '';
        $criterios = $criteriosText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $criteriosText)), fn($v) => $v !== '')) : [];

        // Ruta a la plantilla .docx (ajusta si es necesario)
        $templatePath = storage_path('app/templates/lista_cotejo_template.docx');
        if (!file_exists($templatePath)) {
            abort(404, 'Plantilla no encontrada: ' . $templatePath);
        }

        $templateProcessor = new TemplateProcessor($templatePath);
        // Variables simples: competencia, titulo, criterios (texto con saltos)
        $templateProcessor->setValue('competencia', htmlentities($competencia));
        $templateProcessor->setValue('titulo', htmlentities($titulo));
        // Si tu plantilla espera salto de línea, dejamos texto con saltos
        $templateProcessor->setValue('criterios', htmlentities(implode("\n", $criterios)));

        $tempFile = tempnam(sys_get_temp_dir(), 'lista_cotejo_') . '.docx';
        $templateProcessor->saveAs($tempFile);

        return response()->download($tempFile, ($titulo ?: 'lista_cotejo') . '.docx')->deleteFileAfterSend(true);
    }
    public function vistaPreviaHtml($sesionId, Request $request)
    {
        $sesion = Sesion::with(['aulaCurso.aula', 'aulaCurso.curso', 'docente.persona'])->findOrFail($sesionId);
        $detalle = $sesion->detalle;

        // obtener listas guardadas en BD
        $listas = ListaCotejo::where('sesion_id', $sesion->id)->get();

        // si no hay listas guardadas, intentar generar desde propositos_aprendizaje
        if ($listas->isEmpty() && $detalle?->propositos_aprendizaje) {
            $generated = [];
            foreach ($detalle->propositos_aprendizaje as $prop) {
                // sólo generar si hay criterios
                $criterios = [];
                if (!empty($prop['criterios'])) {
                    $criterios = is_array($prop['criterios'])
                        ? array_values(array_filter(array_map('trim', $prop['criterios'])))
                        : array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', (string)$prop['criterios']))));
                }
                if (empty($criterios)) continue;

                $generated[] = (object)[
                    'id' => null,
                    'sesion_id' => $sesion->id,
                    'competencia_id' => $prop['competencia_id'] ?? null,
                    'titulo' => $prop['lista_cotejo_titulo'] ?? ($prop['competencia_id'] ? Competencia::find($prop['competencia_id'])?->nombre : 'Lista de cotejo'),
                    'niveles' => $prop['lista_cotejo_niveles'] ?? null,
                    'descripcion' => implode("\n", $criterios),
                    'is_generated' => true,
                ];
            }
            $listas = collect($generated);
        } else {
            // marcar listas reales
            $listas->transform(fn($l) => tap($l, fn($x) => $x->is_generated = false));
        }

        // obtener estudiantes de la sesión (varios fallback)
        $estudiantes = collect();
        try {
            if ($sesion->aulaCurso) {
                // intento modelos comunes: matrciculas, alumnos, estudiantes
                if (method_exists($sesion->aulaCurso, 'matriculas')) {
                    $estudiantes = $sesion->aulaCurso->matriculas()->with(['alumno.persona'])->get()->map(function($m) {
                        $nombre = $m->alumno?->persona ? trim(($m->alumno->persona->nombre ?? '') . ' ' . ($m->alumno->persona->apellido ?? '')) : ($m->nombre ?? null);
                        return ['id' => $m->id, 'nombre' => $nombre];
                    });
                }
                // fallback: aula->alumnos
                if ($estudiantes->isEmpty() && $sesion->aulaCurso->aula && method_exists($sesion->aulaCurso->aula, 'alumnos')) {
                    $estudiantes = $sesion->aulaCurso->aula->alumnos()->with('persona')->get()->map(function($a) {
                        $nombre = $a->persona ? trim(($a->persona->nombre ?? '') . ' ' . ($a->persona->apellido ?? '')) : ($a->nombre ?? null);
                        return ['id' => $a->id, 'nombre' => $nombre];
                    });
                }
            }
        } catch (\Throwable $e) {
            // si falla, dejamos la colección vacía
            $estudiantes = collect();
        }

        // si no hay estudiantes, dejar una fila vacía para la vista
        if ($estudiantes->isEmpty()) {
            $estudiantes = collect([['id' => null, 'nombre' => '']]);
        }

        // preparar arrays de criterios por lista
        $listas->transform(function($l) {
            $criteriosText = $l->descripcion ?? '';
            $criterios = $criteriosText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $criteriosText)), fn($v) => $v !== '')) : [];
            // niveles: intentar obtener 3 niveles separados por comas o saltos
            $nivelesText = $l->niveles ?? '';
            $niveles = $nivelesText !== '' ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $nivelesText)), fn($v) => $v !== '')) : [];
            if (count($niveles) < 3) $niveles = ['Bajo','Medio','Alto']; // default
            $l->criterios_array = $criterios;
            $l->niveles_array = array_slice($niveles, 0, 3);
            // nombre de competencia
            $l->competencia_nombre = $l->competencia_id ? Competencia::find($l->competencia_id)?->nombre : null;
            return $l;
        });

        return view('filament.docente.documentos.listas_cotejo.vista-previa-listas-cotejo', [
            'sesion' => $sesion,
            'listas' => $listas,
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
