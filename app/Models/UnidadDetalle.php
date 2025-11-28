<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadDetalle extends Model
{
    use HasFactory;

    protected $table = 'unidad_detalles';

    protected $fillable = [
        'unidad_id',
        'contenido',
        'enfoques',
        'materiales_basicos',
        'recursos',
        'cronograma',
    ];

    protected $casts = [
        'contenido' => 'array',
        'enfoques' => 'array',
        'cronograma' => 'array',
    ];

    /** 🔗 Relación con Unidad */
    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    /**
     * El método "booting" de Eloquent.
     * Aquí registramos el observador para el evento "saving".
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Escuchamos el evento 'saving' para sanear el JSON del cronograma
        static::saving(function ($unidadDetalle) {
            $unidadDetalle->sanearCronogramaJson();
        });
    }

    /**
     * Sanea el JSON del cronograma si detecta el formato de edición basado en objetos (con UUIDs).
     * Convierte el JSON de objeto (UUIDs como claves) a array (formato original).
     *
     * @return void
     */
    protected function sanearCronogramaJson()
    {
        // El campo 'cronograma' es un array por el $casts,
        // pero necesitamos trabajar con el atributo crudo si viene como string JSON
        $cronograma = $this->getAttribute('cronograma');

        // Si el cronograma es un string JSON (que podría venir al guardar desde la petición)
        // y comienza con '{', es el formato "dañado" que queremos corregir.
        if (is_string($cronograma) && str_starts_with(trim($cronograma), '{')) {
            // Decodificamos el JSON
            $data = json_decode($cronograma, true);
        } elseif (is_array($cronograma) && count($cronograma) > 0 && array_keys($cronograma)[0] !== 0) {
            // Si ya es un array (por el $casts) y sus claves NO son numéricas secuenciales (0, 1, 2...),
            // significa que tiene las claves UUID.
            $data = $cronograma;
        } else {
            // El formato es correcto (un array de arrays o JSON que empieza con '['), no hacemos nada.
            return;
        }

        // Si $data no es null (decodificación exitosa) y es un array:
        if (is_array($data)) {
            $cronogramaSaneado = [];

            // Iterar sobre las claves (que son los UUIDs) para obtener el valor (el objeto de la semana)
            foreach ($data as $semanaUuid => $semanaData) {
                // Dentro de cada semana, el array 'dias' también tiene UUIDs como claves,
                // por lo que debemos procesarlo también.
                if (isset($semanaData['dias']) && is_array($semanaData['dias'])) {
                    $diasSaneados = [];
                    foreach ($semanaData['dias'] as $diaUuid => $diaData) {
                        // Dentro de cada dia, el array 'sesiones' también tiene UUIDs como claves
                        if (isset($diaData['sesiones']) && is_array($diaData['sesiones'])) {
                             $sesionesSaneadas = [];
                            foreach ($diaData['sesiones'] as $sesionUuid => $sesionData) {
                                // Las sesiones ya tienen el formato correcto (solo la data)
                                $sesionesSaneadas[] = $sesionData;
                            }
                            $diaData['sesiones'] = $sesionesSaneadas;
                        }

                        // Agregamos el día saneado al array de días
                        $diasSaneados[] = $diaData;
                    }
                    $semanaData['dias'] = $diasSaneados;
                }

                // Agregamos la semana saneada al array final
                $cronogramaSaneado[] = $semanaData;
            }

            // Asignamos el array saneado al atributo 'cronograma'.
            // Eloquent lo convertirá a JSON con el formato array ([...]) gracias a '$casts = [...]'.
            $this->setAttribute('cronograma', $cronogramaSaneado);
        }
    }
}