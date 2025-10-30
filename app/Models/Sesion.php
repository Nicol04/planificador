<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    use HasFactory;
    protected $table = 'sesions';
    protected $fillable = [
        'fecha',
        'dia',
        'titulo',
        'tema',
        'tiempo_estimado',
        'proposito_sesion',
        'aula_curso_id',
        'docente_id',
        'public',
    ];
    protected $casts = [
        'fecha' => 'date',
        'public' => 'boolean',
    ];

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function aulaCurso()
    {
        return $this->belongsTo(AulaCurso::class);
    }
    public function detalles()
    {
        return $this->hasMany(SesionDetalle::class);
    }

    public function detalle()
    {
        return $this->hasOne(SesionDetalle::class);
    }

    // Nueva relación para guardar momentos (sesion_momentos)
    public function momentos()
    {
        return $this->hasMany(SesionMomento::class);
    }
    public function listasCotejos()
    {
        return $this->hasMany(\App\Models\ListaCotejo::class, 'sesion_id');
    }

    // Accesor: URL de imagen, devuelve imagen guardada o placeholder
    public function getImagenUrlAttribute()
    {
        // Si existe un campo 'imagen' o 'imagen_path' ajusta según tu DB
        if (!empty($this->imagen)) {
            return asset('storage/' . ltrim($this->imagen, '/'));
        }
        if (!empty($this->imagen_path)) {
            return asset('storage/' . ltrim($this->imagen_path, '/'));
        }
        // fallback público
        return 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1200&auto=format&fit=crop';
    }

    // Accesor: URL de descarga (endpoint esperado en backend)
    public function getDownloadUrlAttribute()
    {
        // Ajusta la ruta si tu aplicación usa named routes
        return url("/sesiones/{$this->id}/download");
    }
}
