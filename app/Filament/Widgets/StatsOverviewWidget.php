<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\FichaAprendizaje;
use App\Models\Plantilla;
use App\Models\Sesion;
use App\Models\Unidad;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPublicaciones = FichaAprendizaje::where('public', true)->count() +
                             Sesion::where('public', true)->count() +
                             Unidad::where('public', true)->count();

        return [
            Stat::make('Total Docentes', User::role('docente')->count())
                ->description('Docentes registrados')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('Total Estudiantes', Estudiante::count())
                ->description('Estudiantes registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([3, 5, 7, 8, 6, 9, 10]),
            
            Stat::make('Total Publicaciones', $totalPublicaciones)
                ->description('Fichas, Sesiones y Unidades públicas')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('success'),
            
            Stat::make('Fichas de Aprendizaje', FichaAprendizaje::count())
                ->description('Fichas creadas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            
            Stat::make('Plantillas', Plantilla::count())
                ->description('Plantillas disponibles')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
            
            Stat::make('Total Sesiones', Sesion::count())
                ->description('Sesiones programadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('danger'),
            
            Stat::make('Total Unidades', Unidad::count())
                ->description('Unidades didácticas')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('indigo'),
        ];
    }
}
