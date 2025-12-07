<?php

namespace App\Http\Controllers;

use App\Models\FichaAprendizaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para previsualizar/imprimir Fichas de Aprendizaje
 * Renderiza los ejercicios en HTML sin necesidad de JavaScript
 */
class FichaAprendizajePreviewController extends Controller
{
    /**
     * Mostrar la vista previa de una ficha para impresión
     */
    public function preview(int $fichaId)
    {
        try {
            $ficha = FichaAprendizaje::with('ejercicios')->findOrFail($fichaId);

            Log::info("📄 [Preview] Generando vista previa de Ficha ID: {$fichaId}");

            // Renderizar cada ejercicio a HTML
            $ejerciciosHtml = [];
            foreach ($ficha->ejercicios as $ejercicio) {
                $ejerciciosHtml[] = $this->renderEjercicio($ejercicio);
            }

            return view('filament.docente.pages.previewficha', [
                'ficha' => $ficha,
                'ejerciciosHtml' => $ejerciciosHtml
            ]);

        } catch (\Exception $e) {
            Log::error("❌ [Preview] Error: " . $e->getMessage());
            abort(404, 'Ficha no encontrada');
        }
    }

    /**
     * Renderizar un ejercicio según su tipo
     */
    private function renderEjercicio($ejercicio): string
    {
        $tipo = $ejercicio->tipo;
        $contenido = $ejercicio->contenido;

        Log::info("🎨 [Preview] Renderizando ejercicio tipo: {$tipo}");

        switch ($tipo) {
            case 'SelectionExercise':
                return $this->renderSelectionExercise($contenido);
            
            case 'ClassificationExercise':
                return $this->renderClassificationExercise($contenido);
            
            case 'ClozeExercise':
                return $this->renderClozeExercise($contenido);
            
            case 'ReflectionExercise':
                return $this->renderReflectionExercise($contenido);
            
            default:
                return "<div class='text-red-600 p-4'>Tipo de ejercicio desconocido: {$tipo}</div>";
        }
    }

    /**
     * Renderizar SelectionExercise
     */
    private function renderSelectionExercise(array $contenido): string
    {
        $title = $contenido['title'] ?? 'Sin título';
        $description = $contenido['description'] ?? '';
        $options = $contenido['options'] ?? [];

        $html = "<div class='mb-8'>";
        
        // Título
        $html .= "<h2 class='text-3xl font-extrabold text-slate-900 mb-3'>{$title}</h2>";
        
        // Descripción
        if ($description) {
            $html .= "<p class='text-base text-slate-600 mb-6'>{$description}</p>";
        }

        // Grid de opciones
        $html .= "<div class='grid grid-cols-3 gap-6'>";
        
        foreach ($options as $idx => $opt) {
            $imageSrc = $opt['imageSrc'] ?? '';
            $text = $opt['text'] ?? '';
            
            $html .= "<div class='bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-5'>";
            
            // Badge con número
            $html .= "<div class='relative mb-2'>";
            $html .= "<div class='absolute -top-3 -left-3 w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg z-10'>" . ($idx + 1) . "</div>";
            
            // Imagen
            $html .= "<div class='w-full aspect-square rounded-xl overflow-hidden border border-slate-300 shadow-md flex items-center justify-center'>";
            $html .= "<img src='{$imageSrc}' alt='{$text}' class='max-w-full max-h-full object-contain' />";
            $html .= "</div>";
            $html .= "</div>";
            
            // Texto
            $html .= "<div class='mt-1 text-center'>";
            $html .= "<p class='text-sm font-medium text-slate-800'>{$text}</p>";
            $html .= "</div>";
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Renderizar ClassificationExercise
     */
    private function renderClassificationExercise(array $contenido): string
    {
        $title = $contenido['title'] ?? 'Sin título';
        $description = $contenido['description'] ?? '';
        $items = $contenido['items'] ?? [];

        // Shuffle images and texts separately to add difficulty
        $images = array_column($items, 'imageSrc');
        $texts = array_column($items, 'text');
        shuffle($images);
        shuffle($texts);
        // Rebuild items with shuffled pairs
        $shuffledItems = [];
        foreach ($images as $idx => $image) {
            $shuffledItems[] = [
                'imageSrc' => $image,
                'text' => $texts[$idx]
            ];
        }
        $items = $shuffledItems;

        $html = "<div class='mb-8'>";
        
        // Título
        $html .= "<h2 class='text-3xl font-extrabold text-slate-900 mb-3'>{$title}</h2>";
        
        // Descripción
        if ($description) {
            $html .= "<p class='text-base text-slate-600 mb-6'>{$description}</p>";
        }

        // Lista de items
        $html .= "<div class='space-y-3'>";
        
        foreach ($items as $idx => $item) {
            $imageSrc = $item['imageSrc'] ?? '';
            $text = $item['text'] ?? '';
            
            $html .= "<div class='flex items-center gap-4 p-4 bg-white border-2 border-slate-200 rounded-xl'>";
            
            // Sección izquierda
            $html .= "<div class='flex items-center gap-3'>";
            
            // Badge
            $html .= "<div class='flex-shrink-0 w-8 h-8 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg bg-slate-500'>" . ($idx + 1) . "</div>";
            
            // Imagen
            $html .= "<div class='w-40 h-40 rounded-lg overflow-hidden border-2 border-slate-300 shadow-md flex items-center justify-center'>";
            $html .= "<img src='{$imageSrc}' alt='{$text}' class='w-full h-full object-cover' />";
            $html .= "</div>";
            
            $html .= "</div>";
            
            // Sección derecha
            $html .= "<div class='flex-1 flex items-center gap-3'>";
            $html .= "<div class='hidden md:block w-12 h-0.5 bg-gradient-to-r from-slate-300 to-slate-400'></div>";
            $html .= "<div class='flex-1 px-4 py-3 text-base font-medium text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-lg'>{$text}</div>";
            $html .= "</div>";
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Renderizar ClozeExercise
     */
    private function renderClozeExercise(array $contenido): string
    {
        $title = $contenido['title'] ?? 'Sin título';
        $description = $contenido['description'] ?? '';
        $items = $contenido['items'] ?? [];

        $html = "<div class='mb-8'>";
        
        // Título
        $html .= "<h2 class='text-3xl font-extrabold text-slate-900 mb-3'>{$title}</h2>";
        
        // Descripción
        if ($description) {
            $html .= "<p class='text-base text-slate-600 mb-6'>{$description}</p>";
        }

        // Grid de items
        $html .= "<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>";
        
        foreach ($items as $idx => $item) {
            $imageSrc = $item['imageSrc'] ?? '';
            $placeholder = $item['placeholder'] ?? '_________';
            
            $html .= "<div class='bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-2xl p-5 flex flex-col items-center'>";
            
            // Header
            $html .= "<div class='flex items-center gap-3 mb-4'>";
            $html .= "<div class='w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-full flex items-center justify-center font-bold text-sm shadow-lg'>" . ($idx + 1) . "</div>";
            $html .= "<span class='text-sm font-semibold text-slate-700'>Completa la frase</span>";
            $html .= "</div>";
            
            // Imagen
            $html .= "<div class='mb-4 flex justify-center w-full'>";
            $html .= "<div class='w-40 h-40 rounded-xl overflow-hidden border-3 border-slate-300 shadow-lg flex items-center justify-center'>";
            $html .= "<img src='{$imageSrc}' alt='' class='w-full h-full object-cover' />";
            $html .= "</div>";
            $html .= "</div>";
            
            // Texto/placeholder
            $html .= "<div class='w-full'>";
            $html .= "<div class='w-full px-3 py-2 text-center text-sm font-bold text-slate-800 bg-white border-2 border-slate-300 rounded-lg min-h-[3rem] flex items-center justify-center whitespace-pre-wrap'>{$placeholder}</div>";
            $html .= "</div>";
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Renderizar ReflectionExercise
     */
    private function renderReflectionExercise(array $contenido): string
    {
        $title = $contenido['title'] ?? 'Sin título';
        $description = $contenido['description'] ?? '';
        $text = $contenido['text'] ?? '';
        $imageSrc = $contenido['imageSrc'] ?? '';
        $questions = $contenido['questions'] ?? [];

        $html = "<div class='mb-8'>";
        
        // Título
        $html .= "<h2 class='text-3xl font-extrabold text-slate-900 mb-3'>{$title}</h2>";
        
        // Descripción
        if ($description) {
            $html .= "<p class='text-base text-slate-600 mb-6'>{$description}</p>";
        }

        // Card de texto
        $html .= "<div class='bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-2xl p-6 mb-6 shadow-md'>";
        
        // Header
        $html .= "<div class='flex items-center gap-2 mb-4 pb-3 border-b-2 border-slate-200'>";
        $html .= "<h3 class='text-lg font-bold text-slate-800'>Texto de Lectura</h3>";
        $html .= "</div>";
        
        // Imagen si existe
        if ($imageSrc) {
            $html .= "<div class='mb-6 flex justify-center'>";
            $html .= "<div class='w-72 h-72 rounded-2xl overflow-hidden border-3 border-slate-300 shadow-xl'>";
            $html .= "<img src='{$imageSrc}' alt='{$title}' class='w-full h-full object-cover' />";
            $html .= "</div>";
            $html .= "</div>";
        }
        
        // Texto
        $html .= "<div class='w-full px-4 py-4 text-base leading-relaxed text-slate-800 bg-white border-2 border-slate-300 rounded-xl whitespace-pre-wrap'>{$text}</div>";
        
        $html .= "</div>";
        
        // Preguntas
        $html .= "<div class='flex items-center gap-2 mb-4 mt-8'>";
        $html .= "<div class='w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center text-white text-lg shadow-lg'>❓</div>";
        $html .= "<h3 class='text-lg font-bold text-slate-800'>Preguntas de Reflexión</h3>";
        $html .= "</div>";
        
        $html .= "<div class='space-y-4'>";
        
        foreach ($questions as $idx => $question) {
            $html .= "<div class='bg-white border-2 border-slate-200 rounded-xl p-4'>";
            $html .= "<div class='flex items-start gap-3'>";
            $html .= "<div class='flex-shrink-0 w-7 h-7 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-full flex items-center justify-center font-bold text-xs shadow'>" . ($idx + 1) . "</div>";
            $html .= "<div class='flex-1'>";
            $html .= "<p class='text-base font-medium text-slate-800 mb-3'>{$question}</p>";
            $html .= "<div class='w-full h-20 bg-slate-50 border border-slate-300 rounded-lg'></div>";
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
        }
        
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }
}