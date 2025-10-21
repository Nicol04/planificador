<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Pages;
use Filament\Widgets;
use App\Filament\Resources\Docente\PlanificacionResource;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

class DocentePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('docente')
            ->path('docente')
            ->login(\Filament\Pages\Auth\Login::class)
            ->authGuard('docente')
            ->brandName('Panel Docente')
            ->colors([
                'primary' => '#2563eb',
            ])
            ->favicon(asset('assets/img/logo_colegio.png'))
            ->sidebarCollapsibleOnDesktop()
            // Logo de SAT Industriales
            ->brandLogo(asset('assets/img/logo_colegio.png'))
            ->brandLogoHeight('10rem')
            ->discoverResources(in: app_path('Filament/Resources/Docente'), for: 'App\\Filament\\Resources\\Docente')
            ->discoverPages(in: app_path('Filament/Pages/Docente'), for: 'App\\Filament\\Pages\\Docente')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(), // permisos por rol
            ]);
            
    }
}
