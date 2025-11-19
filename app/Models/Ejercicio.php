<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ejercicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'ficha_aprendizaje_id',
        'contenido',
    ];

    /**
     * Cast del campo contenido a array/JSON
     */
    protected $casts = [
        'contenido' => 'array',
    ];

    public function fichaAprendizaje()
    {
        return $this->belongsTo(FichaAprendizaje::class);
    }
}