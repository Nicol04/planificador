<?php

namespace App\Filament\Docente\Resources\UnidadResource\Schemas;

use Filament\Forms;

class EnfoquesSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Enfoques Transversales')
                ->description('Valores y actitudes para esta unidad')
                ->icon('heroicon-o-star')
                ->schema([
                    Forms\Components\Repeater::make('enfoques')
                        ->label('')
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Select::make('enfoque_id')
                                        ->label('🔍 Enfoque')
                                        ->options(\App\Models\EnfoqueTransversal::pluck('nombre', 'id'))
                                        ->searchable()
                                        ->placeholder('Seleccionar')
                                        ->required()
                                        ->reactive()
                                        ->native(false)
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            if ($state && empty($get('valores'))) {
                                                $set('valores', [['valor' => null, 'actitud' => '']]);
                                            }
                                        })
                                        ->prefixIcon('heroicon-o-sparkles'),

                                    Forms\Components\Repeater::make('valores')
                                        ->label('💎 Valores y Actitudes')
                                        ->schema([
                                            Forms\Components\Select::make('valor')
                                                ->label('💡 Valor')
                                                ->options(function (callable $get) {
                                                    $enfoqueId = $get('../../enfoque_id');
                                                    if (!$enfoqueId) return [];
                                                    
                                                    $enfoque = \App\Models\EnfoqueTransversal::find($enfoqueId);
                                                    $todosValores = collect($enfoque->valores_actitudes ?? [])
                                                        ->pluck('data.Valores', 'data.Valores');

                                                    // Obtener valores ya seleccionados
                                                    $valoresSeleccionados = collect($get('../../valores') ?? [])
                                                        ->pluck('valor')
                                                        ->filter()
                                                        ->toArray();

                                                    // Filtrar para no mostrar los ya seleccionados
                                                    return $todosValores->reject(fn($valor) => in_array($valor, $valoresSeleccionados))->toArray();
                                                })
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    $enfoqueId = $get('../../enfoque_id');
                                                    if (!$enfoqueId || !$state) return;

                                                    $enfoque = \App\Models\EnfoqueTransversal::find($enfoqueId);
                                                    $valorData = collect($enfoque->valores_actitudes ?? [])
                                                        ->firstWhere('data.Valores', $state);

                                                    if ($valorData) {
                                                        $set('actitud', $valorData['data']['Actitudes'] ?? '');
                                                    }
                                                })
                                                ->searchable()
                                                ->native(false)
                                                ->required()
                                                ->disabled(fn ($get) => !$get('../../enfoque_id'))
                                                ->helperText(fn ($get) => !$get('../../enfoque_id') ? 'Selecciona primero un enfoque' : '')
                                                ->prefixIcon('heroicon-o-heart'),

                                            Forms\Components\Textarea::make('actitud')
                                                ->label('✅ Actitud observable')
                                                ->rows(2)
                                                ->placeholder('Se completa automáticamente al seleccionar el valor')
                                                ->disabled()
                                                ->dehydrated()
                                                ->visible(fn ($get) => !empty($get('valor'))),
                                        ])
                                        ->defaultItems(1)
                                        ->addActionLabel('➕ Agregar valor')
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['valor'] ?? 'Nuevo valor')
                                        ->reorderable()
                                        ->deleteAction(fn ($action) => $action->color('danger')),
                                ])
                                ->extraAttributes([
                                    'style' => 'background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%); padding: 1.5rem; border-radius: 0.75rem; border: 2px solid #fbbf24;'
                                ]),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('➕ Agregar Enfoque')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            $state['enfoque_id'] 
                                ? '🌟 ' . \App\Models\EnfoqueTransversal::find($state['enfoque_id'])?->nombre 
                                : '✨ Nuevo Enfoque'
                        )
                        ->reorderable()
                        ->deleteAction(fn ($action) => $action->requiresConfirmation())
                        ->columnSpanFull(),
                ])
                ->collapsible()
        ];
    }
}