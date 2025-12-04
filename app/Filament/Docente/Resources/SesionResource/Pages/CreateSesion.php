<?php

namespace App\Filament\Docente\Resources\SesionResource\Pages;

use App\Filament\Docente\Resources\SesionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\ListaCotejo;
use Illuminate\Support\Facades\Log;
use App\Models\SesionMomento;
class CreateSesion extends CreateRecord
{
    protected static string $resource = SesionResource::class;
    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['data'])) {
            $data = $data['data'];
        }
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
        $state = $this->form->getState();
        $formData = $this->data['data'] ?? $state;
        $sesion = $this->record;

        // Preparar datos de propósitos de aprendizaje
        $propositos = [];
        if (!empty($formData['competencias'])) {
            foreach ($formData['competencias'] as $comp) {
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

                // NORMALIZAR estándares -> garantizar array de IDs
                $estRaw = $comp['estandares'] ?? [];
                if (!is_array($estRaw)) {
                    $estRaw = $estRaw === '' ? [] : (array) $estRaw;
                }
                $estIds = [];
                foreach ($estRaw as $e) {
                    if (is_numeric($e)) {
                        $estIds[] = (int) $e;
                    } elseif (is_string($e) && trim($e) !== '') {
                        // intentar buscar por descripción -> obtener id
                        $foundId = \App\Models\Estandar::where('descripcion', trim($e))->value('id');
                        if ($foundId) {
                            $estIds[] = (int) $foundId;
                        }
                    }
                }
                $estIds = array_values(array_unique(array_filter($estIds)));

                $propositos[] = [
                    'competencia_id' => $comp['competencia_id'] ?? null,
                    'capacidades' => $comp['capacidades'] ?? [],
                    'estandares' => $estIds,
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

        // Crear detalle de sesion
        $sesion->detalles()->create([
            'propositos_aprendizaje' => $propositos,
            'transversalidad' => $transversalidad,
            'evidencia' => $formData['evidencias'] ?? '',
        ]);

        // Guardar momentos: soporta formato nuevo (assoc) y antiguo (lista)
        $momentosRaw = $formData['momentos_data'] ?? null;
        if ($momentosRaw) {
            // Si viene como JSON string intentar decodificar
            $momentos = is_string($momentosRaw) ? json_decode($momentosRaw, true) : $momentosRaw;

            $inicio = $desarrollo = $cierre = null;

            // Formato nuevo: array asociativo con claves inicio/desarrollo/cierre
            if (is_array($momentos) && (array_key_exists('inicio', $momentos) || array_key_exists('desarrollo', $momentos) || array_key_exists('cierre', $momentos))) {
                $inicio = $momentos['inicio'] ?? null;
                $desarrollo = $momentos['desarrollo'] ?? null;
                $cierre = $momentos['cierre'] ?? null;
            } elseif (is_array($momentos)) {
                // Formato antiguo: lista de objetos con nombre_momento y descripcion
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
                try {
                    $sesion->momento()->create([
                        'inicio' => $inicio,
                        'desarrollo' => $desarrollo,
                        'cierre' => $cierre,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Error guardando SesionMomento: '.$e->getMessage(), [
                        'sesion_id' => $sesion->id,
                        'momentos' => $momentos,
                    ]);
                }
            }
        }
    }
    
}
