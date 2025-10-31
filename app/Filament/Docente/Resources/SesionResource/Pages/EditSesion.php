<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use App\Models\AulaCurso;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

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

            // Normalizar cada propósito para que el formulario lo muestre correctamente
            foreach ($data['competencias'] as $index => $prop) {
                // instrumentos_predefinidos: puede venir como array o string -> dejar string (primer valor) para el Select
                $instPre = $prop['instrumentos_predefinidos'] ?? null;
                if (is_array($instPre)) {
                    $data['competencias'][$index]['instrumentos_predefinidos'] = count($instPre) ? $instPre[0] : null;
                } else {
                    $data['competencias'][$index]['instrumentos_predefinidos'] = $instPre;
                }

                // instrumentos_personalizados: asegurar array
                $data['competencias'][$index]['instrumentos_personalizados'] = is_array($prop['instrumentos_personalizados'] ?? null)
                    ? $prop['instrumentos_personalizados']
                    : (empty($prop['instrumentos_personalizados']) ? [] : (array) $prop['instrumentos_personalizados']);

                // criterios: limpiar y asegurar array
                $criteriosRaw = $prop['criterios'] ?? [];
                if (is_string($criteriosRaw)) {
                    $criteriosArr = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $criteriosRaw)), fn($v) => $v !== ''));
                } elseif (is_array($criteriosRaw)) {
                    $criteriosArr = array_values(array_filter(array_map('trim', $criteriosRaw), fn($v) => $v !== ''));
                } else {
                    $criteriosArr = [];
                }
                $data['competencias'][$index]['criterios'] = $criteriosArr;

                // lista de cotejo: si existe al menos una ListaCotejo para la sesión, prellenamos los campos de la primera encontrada
                try {
                    $lista = \App\Models\ListaCotejo::where('sesion_id', $sesion->id)->first();
                } catch (\Throwable $e) {
                    $lista = null;
                }

                if ($lista) {
                    // marcar como generada y rellenar título/niveles; el Select debe mostrar "Lista de cotejo"
                    $data['competencias'][$index]['generar_lista_cotejo'] = true;
                    $data['competencias'][$index]['lista_cotejo_titulo'] = $lista->titulo;
                    $data['competencias'][$index]['lista_cotejo_niveles'] = $lista->niveles;
                    $data['competencias'][$index]['instrumentos_predefinidos'] = 'Lista de cotejo';
                } else {
                    // si el propósito ya trae datos de lista, respetarlos; si no, aseguramos valores por defecto
                    $data['competencias'][$index]['generar_lista_cotejo'] = !empty($prop['generar_lista_cotejo']);
                    $data['competencias'][$index]['lista_cotejo_titulo'] = $prop['lista_cotejo_titulo'] ?? null;
                    $data['competencias'][$index]['lista_cotejo_niveles'] = $prop['lista_cotejo_niveles'] ?? 'Logrado, En proceso, Destacado';
                }
            }

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

        try {
            $sesion->listasCotejos()->delete();
        } catch (\Throwable $e) {
            Log::error('Error eliminando listasCotejos previas: '.$e->getMessage(), ['sesion_id' => $sesion->id]);
        }
        // Preparar datos de propósitos de aprendizaje
        $propositos = [];
        if (!empty($this->data['competencias'])) {
            foreach ($this->data['competencias'] as $compIndex => $comp) {
                // Combinar instrumentos predefinidos y personalizados
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

                // Normalizar instrumentos
                $instPre = $comp['instrumentos_predefinidos'] ?? null;
                $instPreArr = is_null($instPre) || $instPre === '' ? [] : (is_array($instPre) ? $instPre : [$instPre]);
                $instPersArr = is_array($comp['instrumentos_personalizados'] ?? null) ? $comp['instrumentos_personalizados'] : (array) ($comp['instrumentos_personalizados'] ?? []);
                $instrumentos = array_values(array_filter(array_unique(array_merge($instPreArr, $instPersArr)), fn($v) => trim((string)$v) !== ''));

                // Crear ListaCotejo si corresponde
                if (in_array('Lista de cotejo', $instPreArr, true) && !empty($comp['generar_lista_cotejo'])) {
                    try {
                        $sesion->listasCotejos()->create([
                            'titulo' => $comp['lista_cotejo_titulo'] ?? null,
                            'niveles' => $comp['lista_cotejo_niveles'] ?? null,
                            'descripcion' => !empty($criteriosArr) ? implode("\n", $criteriosArr) : null,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Error creando ListaCotejo en afterSave: '.$e->getMessage(), [
                            'sesion_id' => $sesion->id,
                            'comp_index' => $compIndex,
                        ]);
                    }
                }

                $propositos[] = [
                    'competencia_id' => $comp['competencia_id'] ?? null,
                    'capacidades' => $comp['capacidades'] ?? [],
                    'estandares' => $comp['estandares'] ?? [],
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
            Actions\Action::make('previsualizar_listas')
                ->label('Listas de cotejo')
                ->icon('heroicon-o-eye')
                ->url(route('listas-cotejo.vista.previa', $this->record->id))
                ->openUrlInNewTab(),
        ];
    }
}
