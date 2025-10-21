<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'nombre',
        'grado',
        'secciones',
        'fecha_inicio',
        'fecha_fin',
        'profesores_responsables',
        'situacion_significativa',
        'productos',
    ];

    protected $casts = [
        'secciones' => 'array',
        'profesores_responsables' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /** 🔗 Relación con detalles */
    public function detalles()
    {
        return $this->hasMany(UnidadDetalle::class, 'unidad_id');
    }
    /** 👥 Obtener profesores responsables como colección de usuarios */
    public function getProfesoresAttribute()
    {
        if (empty($this->profesores_responsables)) {
            return collect();
        }

        // Convertir strings a enteros para la consulta
        $profesorIds = array_map('intval', $this->profesores_responsables);

        return User::whereIn('id', $profesorIds)
            ->with('persona')
            ->get();
    }

    /** 📝 Obtener nombres de profesores responsables */
    public function getNombresProfesoresAttribute()
    {
        return $this->profesores->map(function ($user) {
            $persona = $user->persona;
            return trim(($persona?->nombre ?? '') . ' ' . ($persona?->apellido ?? '')) ?: 'Docente sin nombre';
        })->join(', ');
    }
}
