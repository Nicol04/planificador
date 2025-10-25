<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionDetalle extends Model
{
    use HasFactory;
    protected $table = 'sesion_detalles';
    protected $fillable = [
        'sesion_id',
        'propositos_aprendizaje',
        'transversalidad',
        'evidencia',
    ];

    // Esto convierte automáticamente JSON ↔ Array
    protected $casts = [
        'propositos_aprendizaje' => 'array',
        'transversalidad' => 'array',
    ];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }

}
