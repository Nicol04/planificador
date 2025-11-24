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
        $fillData = $data;
        // Obtener curso_id desde aula_curso_id
        if ($sesion->aula_curso_id) {
            $aulaCurso = AulaCurso::find($sesion->aula_curso_id);
            if ($aulaCurso) {
                $fillData['curso_id'] = $aulaCurso->curso_id;
            }
        }

        try {
            $mom = $sesion->momento; // usa la relación momento()
            if ($mom) {
                // Rellenar el array esperado por los campos RichEditor: data.momentos_data.inicio|desarrollo|cierre
                $fillData['momentos_data'] = [
                    'inicio' => $mom->inicio ?? '',
                    'desarrollo' => $mom->desarrollo ?? '',
                    'cierre' => $mom->cierre ?? '',
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error al obtener momentos de la sesión: ' . $e->getMessage());
        }

        if ($detalle) {
            $fillData['competencias'] = $detalle->propositos_aprendizaje ?? [];
            $fillData['evidencias'] = $detalle->evidencia ?? '';

            // Normalizar cada propósito para que el formulario lo muestre correctamente
            foreach ($fillData['competencias'] as $index => $prop) {
                // instrumentos_predefinidos: puede venir como array o string -> dejar string (primer valor) para el Select
                $instPre = $prop['instrumentos_predefinidos'] ?? null;
                if (is_array($instPre)) {
                    $fillData['competencias'][$index]['instrumentos_predefinidos'] = count($instPre) ? $instPre[0] : null;
                } else {
                    $fillData['competencias'][$index]['instrumentos_predefinidos'] = $instPre;
                }

                // instrumentos_personalizados: asegurar array
                $fillData['competencias'][$index]['instrumentos_personalizados'] = is_array($prop['instrumentos_personalizados'] ?? null)
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
                $fillData['competencias'][$index]['criterios'] = $criteriosArr;

                // lista de cotejo: si existe al menos una ListaCotejo para la sesión, prellenamos los campos de la primera encontrada
                try {
                    $lista = \App\Models\ListaCotejo::where('sesion_id', $sesion->id)->first();
                } catch (\Throwable $e) {
                    $lista = null;
                }

                if ($lista) {
                    // marcar como generada y rellenar título/niveles; el Select debe mostrar "Lista de cotejo"
                    $fillData['competencias'][$index]['generar_lista_cotejo'] = true;
                    $fillData['competencias'][$index]['lista_cotejo_titulo'] = $lista->titulo;
                    $fillData['competencias'][$index]['lista_cotejo_niveles'] = $lista->niveles;
                    $fillData['competencias'][$index]['instrumentos_predefinidos'] = 'Lista de cotejo';
                } else {
                    // si el propósito ya trae datos de lista, respetarlos; si no, aseguramos valores por defecto
                    $fillData['competencias'][$index]['generar_lista_cotejo'] = !empty($prop['generar_lista_cotejo']);
                    $fillData['competencias'][$index]['lista_cotejo_titulo'] = $prop['lista_cotejo_titulo'] ?? null;
                    $fillData['competencias'][$index]['lista_cotejo_niveles'] = $prop['lista_cotejo_niveles'] ?? 'Logrado, En proceso, Destacado';
                }
            }

            if ($detalle->transversalidad) {
                $fillData['mostrar_enfoques'] = true;
                $transversal = $detalle->transversalidad;
                $fillData['enfoque_transversal_ids'] = $transversal['enfoque_transversal_ids'] ?? [];
                $fillData['competencias_transversales_ids'] = $transversal['competencias_transversales_ids'] ?? [];
                $fillData['capacidades_transversales_ids'] = $transversal['capacidades_transversales_ids'] ?? [];
                $fillData['desempeno_transversal_ids'] = $transversal['desempeno_transversal_ids'] ?? [];
                $fillData['criterios_transversales'] = $transversal['criterios_transversales'] ?? '';
                $fillData['instrumentos_transversales_ids'] = $transversal['instrumentos_transversales_ids'] ?? [];
                $fillData['instrumentos_transversales_personalizados'] = $transversal['instrumentos_transversales_personalizados'] ?? '';
            }
        }

        return ['data' => $fillData];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $inner = $data['data'];

            $user = $this->record->docente;

            if ($user) {
                $usuarioAula = $user->usuario_aulas()->with('aula')->latest()->first();
                if ($usuarioAula) {
                    $cursoId = $inner['curso_id'] ?? null;
                    
                    if ($cursoId) {
                        $aulaCurso = AulaCurso::where('aula_id', $usuarioAula->aula_id)
                            ->where('curso_id', $cursoId)
                            ->first();
                        
                        if ($aulaCurso) {
                            $inner['aula_curso_id'] = $aulaCurso->id;
                        }
                    }
                }
            }

            $data['data'] = $inner;
            return $data;
        }

        // Fallback: comportamiento previo (por seguridad)
        return parent::mutateFormDataBeforeSave($data);
    }

    protected function afterSave(): void
    {
        $sesion = $this->record;
        $detalle = $sesion->detalle;
        $state = $this->form->getState();
        $formData = $this->data['data'] ?? $state;

        try {
            $sesion->listasCotejos()->delete();
        } catch (\Throwable $e) {
            Log::error('Error eliminando listasCotejos previas: '.$e->getMessage(), ['sesion_id' => $sesion->id]);
        }
        // Preparar datos de propósitos de aprendizaje
        $propositos = [];
        if (!empty($formData['competencias'])) {
            foreach ($formData['competencias'] as $compIndex => $comp) {
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
        if ($formData['mostrar_enfoques'] ?? false) {
            $transversalidad = [
                'enfoque_transversal_ids' => $formData['enfoque_transversal_ids'] ?? [],
                'competencias_transversales_ids' => $formData['competencias_transversales_ids'] ?? [],
                'capacidades_transversales_ids' => $formData['capacidades_transversales_ids'] ?? [],
                'desempeno_transversal_ids' => $formData['desempeno_transversal_ids'] ?? [],
                'criterios_transversales' => $formData['criterios_transversales'] ?? '',
                'instrumentos_transversales_ids' => $formData['instrumentos_transversales_ids'] ?? [],
                'instrumentos_transversales_personalizados' => $formData['instrumentos_transversales_personalizados'] ?? '',
            ];
        }

        // Actualizar detalle de sesión
        if ($detalle) {
            $detalle->update([
                'propositos_aprendizaje' => $propositos,
                'transversalidad' => $transversalidad,
                'evidencia' => $formData['evidencias'] ?? '',
            ]);
        }

        // Guardar/actualizar momentos (soporta formato nuevo y antiguo)
        try {
            $momentosRaw = $formData['momentos_data'] ?? null;
            if ($momentosRaw) {
                $momentos = is_string($momentosRaw) ? json_decode($momentosRaw, true) : $momentosRaw;

                $inicio = $desarrollo = $cierre = null;

                if (is_array($momentos) && (array_key_exists('inicio', $momentos) || array_key_exists('desarrollo', $momentos) || array_key_exists('cierre', $momentos))) {
                    $inicio = $momentos['inicio'] ?? null;
                    $desarrollo = $momentos['desarrollo'] ?? null;
                    $cierre = $momentos['cierre'] ?? null;
                } elseif (is_array($momentos)) {
                    foreach ($momentos as $m) {
                        $nombre = mb_strtolower(trim($m['nombre_momento'] ?? ''));
                        $descripcion = $m['descripcion'] ?? ($m['inicio'] ?? ($m['desarrollo'] ?? ($m['cierre'] ?? null)));
                        if ($nombre === 'inicio') {
                            $inicio = $descripcion;
                        } elseif ($nombre === 'desarrollo') {
                            $desarrollo = $descripcion;
                        } elseif ($nombre === 'cierre' || $nombre === 'conclusion') {
                            $cierre = $descripcion;
                        }
                    }
                }

                if ($inicio || $desarrollo || $cierre) {
                    $momRel = $sesion->momento;
                    if ($momRel) {
                        $momRel->update([
                            'inicio' => $inicio,
                            'desarrollo' => $desarrollo,
                            'cierre' => $cierre,
                        ]);
                    } else {
                        $sesion->momento()->create([
                            'inicio' => $inicio,
                            'desarrollo' => $desarrollo,
                            'cierre' => $cierre,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error guardando/actualizando SesionMomento en afterSave: '.$e->getMessage(), ['sesion_id' => $sesion->id]);
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Acción para Lista de cotejo
        if ($this->sessionHasListaCotejo()) {
            $actions[] = Actions\Action::make('previsualizar_listas')
                ->label('Listas de cotejo')
                ->icon('heroicon-o-eye')
                ->url(route('listas-cotejo.vista.previa', $this->record->id))
                ->openUrlInNewTab();
        }

        // Buscar ficha relacionada a la sesión actual
        $fichaSesion = \App\Models\FichaSesion::where('sesion_id', $this->record->id)->latest()->first();
        if ($fichaSesion && $fichaSesion->fichaAprendizaje) {
            $actions[] = Actions\Action::make('preview')
                ->label('Vista previa ficha de aprendizaje')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn() => route('fichas.preview', ['fichaId' => $fichaSesion->fichaAprendizaje->id]))
                ->openUrlInNewTab();
        }

        return $actions;
    }
    protected function sessionHasListaCotejo(): bool
    {
        try {
            $sesion = $this->record;
            if ($sesion && method_exists($sesion, 'listasCotejos') && $sesion->listasCotejos()->exists()) {
                return true;
            }
            $detalle = $sesion->detalle ?? null;
            if (empty($detalle) || empty($detalle->propositos_aprendizaje)) {
                return false;
            }
            foreach ($detalle->propositos_aprendizaje as $prop) {
                $instPre = $prop['instrumentos_predefinidos'] ?? null;
                if (is_array($instPre) && in_array('Lista de cotejo', $instPre, true)) {
                    return true;
                }
                if (is_string($instPre) && stripos($instPre, 'lista de cotejo') !== false) {
                    return true;
                }
                $inst = $prop['instrumentos'] ?? null;
                if (is_array($inst) && in_array('Lista de cotejo', $inst, true)) {
                    return true;
                }
                if (is_string($inst) && stripos($inst, 'lista de cotejo') !== false) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error comprobando listas de cotejo: '.$e->getMessage(), ['sesion_id' => $this->record->id ?? null]);
        }
        return false;
    }
}
