<?php

namespace App\Exports;

use App\Models\Aula;
use App\Models\Año;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\usuario_aula;
use Filament\Notifications\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportUserAula extends BaseExport implements FromCollection, WithHeadings, WithTitle
{
    protected $aulaId;

    public function __construct($aulaId)
    {
        $aula = Aula::find($aulaId);
        $grado = $aula?->grado ?? 'Sin grado';
        $seccion = $aula?->seccion ?? 'Sin sección';
        parent::__construct(
            titulo: 'Colegio Ann Goulden',
            subtitulo: 'Alumnos del aula - ' . $grado .  $seccion,
            ultimaColumna: 'H',
            colorSubtitulo: 'fdf59e'
        );

        $this->columnasCentradas = ['C', 'D', 'E', 'F', 'G', 'H'];
        $this->aulaId = $aulaId;
    }

    public function collection()
    {
        $aulaId = $this->aulaId;

        // año activo (opcional)
        $año = Año::whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->first();

        // usuarios vinculados vía usuario_aulas (filtramos por año activo si existe)
        $userIdsQuery = usuario_aula::where('aula_id', $aulaId);
        if ($año) {
            $userIdsQuery->where('año_id', $año->id);
        }
        $userIds = $userIdsQuery->pluck('user_id')->unique()->toArray();

        $rows = [];

        // 1) estudiantes asignados por aula_id -> rol "Estudiante"
        $estudiantes = Estudiante::where('aula_id', $aulaId)->get();
        foreach ($estudiantes as $est) {
            $rows[] = [
                'nombres'  => $est->nombres ?? '',
                'apellidos' => $est->apellidos ?? '',
                'rol'      => 'Estudiante',
            ];
        }

        // 2) usuarios vinculados vía usuario_aulas -> rol según relación (ej. 'Docente' u otro)
        if (!empty($userIds)) {
            // intentamos cargar roles si existe la relación
            $users = User::whereIn('id', $userIds)->with(['persona', 'roles'])->get();
            foreach ($users as $user) {
                // obtener nombres/apellidos desde persona si existe, si no separar name
                $nombres = '';
                $apellidos = '';
                $persona = $user->persona ?? null;
                if ($persona) {
                    $nombres = $persona->nombre ?? ($user->name ?? '');
                    $apellidos = $persona->apellido ?? '';
                } else {
                    $parts = preg_split('/\s+/', trim($user->name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                    if (count($parts) === 1) {
                        $nombres = $parts[0];
                        $apellidos = '';
                    } elseif (count($parts) > 1) {
                        $nombres = array_shift($parts);
                        $apellidos = implode(' ', $parts);
                    }
                }

                // determinar rol: si tiene relación roles usamos el primero, si no 'Docente' por defecto
                $rol = 'Docente';
                if ($user->relationLoaded('roles') && $user->roles->isNotEmpty()) {
                    $rol = ucfirst($user->roles->first()->name ?? $rol);
                } elseif (method_exists($user, 'getRoleNames')) {
                    $names = $user->getRoleNames()->toArray();
                    if (!empty($names)) $rol = ucfirst($names[0]);
                }

                $rows[] = [
                    'nombres'  => $nombres,
                    'apellidos' => $apellidos,
                    'rol'      => $rol,
                ];
            }
        }

        // devolver colección para exportar (Nombres, Apellidos, Rol)
        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'Nombres',
            'Apellidos',
            'Rol',
        ];
    }

    public function title(): string
    {
        $aula = Aula::find($this->aulaId);

        $grado = $aula?->grado ?? 'Sin grado';
        $seccion = $aula?->seccion ?? 'Sin sección';

        return "Usuarios - Grado: $grado, Sección: $seccion";
    }
}
