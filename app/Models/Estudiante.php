<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;
    protected $table = 'estudiantes';
    protected $fillable = [
        'nombres',
        'apellidos',
        'aula_id',
    ];
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
}
