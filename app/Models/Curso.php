<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $table = 'cursos';
    protected $fillable = [
        'curso',
        'descripcion',
        'image_url',
    ];
    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
    public function aulas()
    {
        return $this->belongsToMany(Aula::class, 'aula_curso')->withTimestamps();
    }
}
