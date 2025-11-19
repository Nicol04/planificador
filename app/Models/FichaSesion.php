<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FichaSesion extends Model
{
    use HasFactory;

    protected $table = 'ficha_sesion';

    protected $fillable = [
        'ficha_aprendizaje_id',
        'sesion_id',
    ];

    public function fichaAprendizaje()
    {
        return $this->belongsTo(FichaAprendizaje::class);
    }

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }
}