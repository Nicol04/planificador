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

    public function getVistaSeguraAttribute(): string
    {
        $default = 'filament.docente.documentos.asistencias.vista-previa-horizontal';

        // Lista de vistas permitidas (añadir aquí las vistas que vayas a soportar)
        $whitelist = [
            'filament.docente.documentos.asistencias.vista-previa-horizontal',
            'filament.docente.documentos.asistencias.vista-previa-horizontal2',
            'filament.docente.documentos.asistencias.vista-previa-horizontal3',
            
        ];

        // Si en 'archivo' guardas la vista, validar y devolver
        if (!empty($this->archivo) && is_string($this->archivo)) {
            $candidate = trim($this->archivo);
            if (in_array($candidate, $whitelist, true)) {
                return $candidate;
            }
        }

        return $default;
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
