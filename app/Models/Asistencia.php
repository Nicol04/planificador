<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;
    protected $table = 'asistencias';
    protected $fillable = [
        'docente_id',
        'nombre_aula',
        'mes',
        'fecha_inicio',
        'fecha_fin',
        'estudiantes',
    ];
    protected $casts = [
        'estudiantes' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
}