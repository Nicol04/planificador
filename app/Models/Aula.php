<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Aula extends Model
{
    protected $table = 'aulas';
    use HasFactory;
    protected $fillable = [
        'grado',
        'seccion',
        'nivel',
        'cantidad_usuarios',
        'nombre'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'usuario_aulas')
            ->withTimestamps()
            ->withPivot('año_id');
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'aula_curso')->withTimestamps();
    }
    public function getGradoSeccionAttribute()
    {
        return "{$this->grado} - {$this->seccion}";
    }
    public function getDocenteAttribute()
    {
        return $this->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'docente'))
            ->with(['persona'])
            ->first();
    }
    public function actualizarCantidadUsuarios()
    {
        // Si la tabla estudiantes tiene aula_id, contamos estudiantes
        if (Schema::hasColumn('estudiantes', 'aula_id')) {
            $this->cantidad_usuarios = \App\Models\Estudiante::where('aula_id', $this->id)->count();
            $this->save();
            return;
        }

        // Si no existe aula_id, se mantiene la lógica por usuario_aulas (por año activo)
        $año = \App\Models\Año::whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->first();

        $this->cantidad_usuarios = $this->users()
            ->when($año, fn($q) => $q->wherePivot('año_id', $año->id))
            ->count();

        $this->save();
    }
}
