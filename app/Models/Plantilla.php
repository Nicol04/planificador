<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Plantilla extends Model
{
    use HasFactory;

    protected $table = 'plantillas';

    protected $fillable = [
        'user_id',
        'nombre',
        'tipo',
        'archivo',
        'imagen_preview',
        'public',
    ];
    protected $casts = [
        'public' => 'boolean',
    ];

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    /**
     * Relación con el usuario que subió la plantilla
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Usuarios que marcaron esta plantilla como favorita
     */
    public function favoritos()
    {
        return $this->belongsToMany(User::class, 'plantilla_user', 'plantilla_id', 'user_id')
                    ->withTimestamps();
    }

    public function getImagenPreviewUrlAttribute()
    {
        if ($this->imagen_preview) {
            return Storage::url($this->imagen_preview);
        }

        // placeholder simple (ajustar si desea otro)
        return asset('vendor/filament/img/placeholder-image.png');
    }

    /**
     * URL pública del archivo de la plantilla (o null)
     */
    public function getArchivoUrlAttribute()
    {
        if ($this->archivo) {
            return Storage::url($this->archivo);
        }

        return null;
    }
}
