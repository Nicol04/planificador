<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\ListaCotejo;
use Illuminate\Support\Facades\Log;

class CreateSesion extends CreateRecord
{
    protected static string $resource = SesionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user) {
            $usuarioAula = $user->usuario_aulas()->with('aula')->latest()->first();
            if ($usuarioAula) {
                $cursoId = $data['curso_id'] ?? null;

                if ($cursoId) {
                    $aulaCurso = \App\Models\AulaCurso::where('aula_id', $usuarioAula->aula_id)
                        ->where('curso_id', $cursoId)
                        ->first();

                    if ($aulaCurso) {
                        $data['aula_curso_id'] = $aulaCurso->id;
                    }
                }
            }

            $data['docente_id'] = $user->id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $sesion = $this->record;

        // Preparar datos de propósitos de aprendizaje
        $propositos = [];
        if (!empty($this->data['competencias'])) {
            foreach ($this->data['competencias'] as $comp) {
                // Combinar instrumentos predefinidos y personalizados
                // normalizar criterios (ya lo tienes)
                $criteriosRaw = $comp['criterios'] ?? [];
                if (!is_array($criteriosRaw)) {
                    if (is_string($criteriosRaw)) {
                        $criteriosArr = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $criteriosRaw)), fn($v) => $v !== ''));
                    } else {
                        $criteriosArr = [];
                    }
                } else {
                    $criteriosArr = array_values(array_filter(array_map('trim', $criteriosRaw), fn($v) => $v !== ''));
                }

                // normalizar instrumentos
                $instPre = $comp['instrumentos_predefinidos'] ?? null;
                $instPreArr = is_null($instPre) || $instPre === '' ? [] : (is_array($instPre) ? $instPre : [$instPre]);
                $instPersArr = is_array($comp['instrumentos_personalizados'] ?? null) ? $comp['instrumentos_personalizados'] : (array) ($comp['instrumentos_personalizados'] ?? []);
                $instrumentos = array_values(array_filter(array_unique(array_merge($instPreArr, $instPersArr)), fn($v) => trim((string)$v) !== ''));

                // Si se eligió Lista de cotejo y está activo generar_lista_cotejo, crear registro con la relación
                if (in_array('Lista de cotejo', $instPreArr, true) && !empty($comp['generar_lista_cotejo'])) {
                    try {
                        $sesion->listasCotejos()->create([
                            'titulo' => $comp['lista_cotejo_titulo'] ?? null,
                            'niveles' => $comp['lista_cotejo_niveles'] ?? null,
                            'descripcion' => !empty($criteriosArr) ? implode("\n", $criteriosArr) : null,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Error creando ListaCotejo: '.$e->getMessage(), [
                            'sesion_id' => $sesion->id,
                            'comp' => $comp,
                        ]);
                    }
                }
                $propositos[] = [
                    'competencia_id' => $comp['competencia_id'] ?? null,
                    'capacidades' => $comp['capacidades'] ?? [],
                    'desempenos' => $comp['desempenos'] ?? [],
                    'criterios' => $criteriosArr,
                    'instrumentos_predefinidos' => $instPreArr,
                    'instrumentos_personalizados' => $instPersArr,
                    'instrumentos' => $instrumentos,
                ];
            }
        }

        // Preparar datos de transversalidad
        $transversalidad = null;
        if ($this->data['mostrar_enfoques'] ?? false) {
            $transversalidad = [
                'enfoque_transversal_ids' => $this->data['enfoque_transversal_ids'] ?? [],
                'competencias_transversales_ids' => $this->data['competencias_transversales_ids'] ?? [],
                'capacidades_transversales_ids' => $this->data['capacidades_transversales_ids'] ?? [],
                'desempeno_transversal_ids' => $this->data['desempeno_transversal_ids'] ?? [],
                'criterios_transversales' => $this->data['criterios_transversales'] ?? '',
                'instrumentos_transversales_ids' => $this->data['instrumentos_transversales_ids'] ?? [],
                'instrumentos_transversales_personalizados' => $this->data['instrumentos_transversales_personalizados'] ?? '',
            ];
        }

        // Crear detalle de sesion
        $sesion->detalles()->create([
            'propositos_aprendizaje' => $propositos,
            'transversalidad' => $transversalidad,
            'evidencia' => $this->data['evidencias'] ?? '',
        ]);

    }
}
