<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

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
                $instrumentos = array_merge(
                    (array) ($comp['instrumentos_predefinidos'] ?? []),
                    (array) ($comp['instrumentos_personalizados'] ?? [])
                );

                $propositos[] = [
                    'competencia_id' => $comp['competencia_id'] ?? null,
                    'capacidades' => $comp['capacidades'] ?? [],
                    'desempenos' => $comp['desempenos'] ?? [],
                    'criterios' => $comp['criterios'] ?? '',
                    'instrumentos_predefinidos' => $comp['instrumentos_predefinidos'] ?? [],
                    'instrumentos_personalizados' => $comp['instrumentos_personalizados'] ?? [],
                    'instrumentos' => array_values(array_unique($instrumentos)),
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