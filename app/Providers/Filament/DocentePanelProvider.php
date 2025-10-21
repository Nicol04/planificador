<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class DocentePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('docente')
            ->path('docente')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => '#001e4c',
                'secondary' => '#2196f3',
                'accent' => '#705449',
                'danger' => '#f44336',
                'success' => '#b6cf4d',
                'info' => '#03a9f4',
                'warning' => '#ffc326',
            ])
            ->darkmode(false)
            ->brandLogo(asset('assets/img/logo_colegio.png'))
            ->brandLogoHeight('60px')
            ->renderHook('panels::head.end', fn() => '
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
')
            ->renderHook('panels::body.start', fn() => '
                <style>
                    .fi-sidebar{
                        background-color: #81c9fa !important;
                    }
                    .fi-sidebar-item-label{
                        color: white;
                    }
                    .fi-sidebar-group-label{
                        color: #003785 ;
                    }
                    .fi-sidebar-item-icon{
                        color: white !important;
                    }
                    .fi-sidebar-item-active .fi-sidebar-item-label{
                        color: #003785 !important;
                    }
                    .fi-sidebar-item-active .fi-sidebar-item-icon{
                        color: #003785 !important;
                    }
                    .fi-sidebar-item:hover .fi-sidebar-item-label {
                        color: #003785 !important;
                    }
                    .fi-sidebar-item:hover .fi-sidebar-item-icon {
                        color: #003785 !important;
                    }
                    .fi-avatar.object-cover.object-center.fi-circular.rounded-full.h-8.w-8.fi-user-avatar {
                        border: 2px solid #003785 !important;
                        height: 60px !important;
                        width: 60px !important;
                    }
                </style>
            ')
            ->renderHook('panels::body.end', fn() => '
            <script>
                // Función global para compatibilidad
                function previsualizarUnidad(unidadId) {
                    if (typeof abrirModalPrevia === "function") {
                        abrirModalPrevia(unidadId);
                    } else {
                        // Fallback directo
                        const url = `/unidades/${unidadId}/vista-previa?orientacion=vertical`;
                        window.open(url, "vistaPreviaUnidad", "width=1200,height=800,scrollbars=yes,resizable=yes");
                    }
                }
            </script> 
            ')
            ->favicon(asset('assets/img/logo_colegio.png'))
            ->login(\Filament\Pages\Auth\Login::class)
            ->discoverResources(in: app_path('Filament/Docente/Resources'), for: 'App\\Filament\\Docente\\Resources')
            ->discoverPages(in: app_path('Filament/Docente/Pages'), for: 'App\\Filament\\Docente\\Pages')
            ->pages([
                \App\Filament\Docente\Pages\Dashboard::class,
            ])

            ->discoverWidgets(in: app_path('Filament/Docente/Widgets'), for: 'App\\Filament\\Docente\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['es']),
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('Mi perfil')
                    ->setNavigationLabel('Mi perfil')
                    ->setNavigationGroup('Configuración')
                    ->setIcon('heroicon-o-user')
                    ->customProfileComponents([
                        \App\Livewire\CustomProfileComponent::class,
                    ])
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    )->shouldShowDeleteAccountForm(false),

            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => Auth::user()->name)
                    ->url(fn(): string => url('/docente/my-profile'))
                    ->icon('heroicon-m-user-circle')
                    ->visible(function (): bool {
                        return Auth::user()->exists();
                    }),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
