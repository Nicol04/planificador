<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Assets\Js;
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

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->sidebarCollapsibleOnDesktop() // INTEGRAR SIDEBAR
            ->default()
            ->brandName('Panel administrativo')
            ->id('dashboard')
            ->path('dashboard')
            ->login(\Filament\Pages\Auth\Login::class)
            ->favicon(asset('assets/img/logo_colegio.png'))
            ->colors([
                'primary' => '#03a9f4',
                'secondary' => '#c27e51',
                'accent' => '#705449',
                'danger' => '#f44336',
                'success' => '#b6cf4d',
                'info' => '#03a9f4',
                'warning' => '#ffc326',
            ])
            ->font('Poppins')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->profile()
            ->assets([
                Js::make('flowbite', 'https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.0/flowbite.min.js'),
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
                ])->shouldShowDeleteAccountForm(false),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => Auth::user()->name)
                    ->url(fn(): string => url('/dashboard/my-profile')) // URL estática
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
