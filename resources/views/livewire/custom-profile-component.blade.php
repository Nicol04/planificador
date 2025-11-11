<div>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="fi-form-actions">
            <div class="flex flex-row-reverse flex-wrap items-center gap-3 fi-ac">
                <x-filament::button type="submit" color="primary">
                    Guardar cambios
                </x-filament::button>
            </div>
        </div>
    </x-filament-panels::form>

    <x-filament-actions::modals />
</div>
