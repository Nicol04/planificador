<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'asistencias';
    protected $fillable = [
        'docente_id',
        'plantilla_id',
        'nombre_aula',
        'mes',
        'anio',
        'dias_no_clase',
    ];
    protected $casts = [
        'dias_no_clase' => 'array',
    ];

    public function docente()
    {
        return $this->belongsTo(User::class);
    }
    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }

    /**
     * Cuenta las semanas calculadas
     */
    public function countWeeks(): int
    {
        // Si el modelo tiene mes y año válidos, generar la matriz y contar semanas.
        if (!empty($this->mes) && !empty($this->anio) && is_numeric($this->mes) && is_numeric($this->anio)) {
            try {
                return count(self::generateWeeksMatrix((int)$this->mes, (int)$this->anio));
            } catch (\Throwable $e) {
                // En caso de error defensivo, devolver 0.
                return 0;
            }
        }
        // Fallback seguro
        return 0;
    }

    /**
     * Plantilla de semana vacía (L..V)
     */
    protected function emptyWeekTemplate(): array
    {
        return [
            'L'  => ['date' => null, 'is_class_day' => false],
            'Ma' => ['date' => null, 'is_class_day' => false],
            'Mi' => ['date' => null, 'is_class_day' => false],
            'J'  => ['date' => null, 'is_class_day' => false],
            'V'  => ['date' => null, 'is_class_day' => false],
        ];
    }

    /**
     * Clase CSS sugerida para un día (por ejemplo para pintar azul días sin clase)
     * Aquí se puede extender para consultar feriados o calendario por aula.
     */
    public function cssClassForDay(array $day = []): string
    {
        return ($day['is_class_day'] ?? false) ? '' : 'no-class';
    }

    /**
     * Genera una matriz de semanas (L..V) para un mes y año dados.
     * Cada elemento de la matriz es ['L'=>['date'=>..., 'is_class_day'=>bool], ... ]
     */
    public static function generateWeeksMatrix(int $mes, int $anio): array
    {
        $startOfMonth = Carbon::create($anio, $mes, 1)->startOfMonth()->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Empezamos en el lunes de la semana que contiene el primer día del mes
        $current = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);

        $weeks = [];
        while ($current->lessThanOrEqualTo($endOfMonth)) {
            $week = [];
            $names = ['L', 'Ma', 'Mi', 'J', 'V'];
            for ($d = 0; $d < 5; $d++) {
                $date = $current->copy()->addDays($d);
                $inMonth = $date->between($startOfMonth, $endOfMonth);
                $week[$names[$d]] = [
                    'date' => $inMonth ? $date->toDateString() : null,
                    'is_class_day' => $inMonth, // por defecto, si está en el mes se considera día de clase
                ];
            }
            $weeks[] = $week;
            $current->addWeek();
        }

        return $weeks;
    }
}
