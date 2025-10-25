<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use App\Models\AulaCurso;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSesion extends EditRecord
{
    protected static string $resource = SesionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar datos del detalle de sesión
        $sesion = $this->record;
        $detalle = $sesion->detalle;

        // Obtener curso_id desde aula_curso_id
        if ($sesion->aula_curso_id) {
            $aulaCurso = AulaCurso::find($sesion->aula_curso_id);
            if ($aulaCurso) {
                $data['curso_id'] = $aulaCurso->curso_id;
            }
        }

        if ($detalle) {
            $data['competencias'] = $detalle->propositos_aprendizaje ?? [];
            $data['evidencias'] = $detalle->evidencia ?? '';

            if ($detalle->transversalidad) {
                $data['mostrar_enfoques'] = true;
                $transversal = $detalle->transversalidad;
                $data['enfoque_transversal_ids'] = $transversal['enfoque_transversal_ids'] ?? [];
                $data['competencias_transversales_ids'] = $transversal['competencias_transversales_ids'] ?? [];
                $data['capacidades_transversales_ids'] = $transversal['capacidades_transversales_ids'] ?? [];
                $data['desempeno_transversal_ids'] = $transversal['desempeno_transversal_ids'] ?? [];
                $data['criterios_transversales'] = $transversal['criterios_transversales'] ?? '';
                $data['instrumentos_transversales_ids'] = $transversal['instrumentos_transversales_ids'] ?? [];
                $data['instrumentos_transversales_personalizados'] = $transversal['instrumentos_transversales_personalizados'] ?? '';
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->docente;

        if ($user) {
            $usuarioAula = $user->usuario_aulas()->with('aula')->latest()->first();
            if ($usuarioAula) {
                $cursoId = $data['curso_id'] ?? null;
                
                if ($cursoId) {
                    $aulaCurso = AulaCurso::where('aula_id', $usuarioAula->aula_id)
                        ->where('curso_id', $cursoId)
                        ->first();
                    
                    if ($aulaCurso) {
                        $data['aula_curso_id'] = $aulaCurso->id;
                    }
                }
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $sesion = $this->record;
        $detalle = $sesion->detalle;

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

        // Actualizar detalle de sesión
        if ($detalle) {
            $detalle->update([
                'propositos_aprendizaje' => $propositos,
                'transversalidad' => $transversalidad,
                'evidencia' => $this->data['evidencias'] ?? '',
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
