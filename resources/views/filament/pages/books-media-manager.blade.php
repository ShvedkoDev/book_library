<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Form --}}
        <div class="filament-forms-component-wrapper">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit">
                        Upload Files
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Files Table --}}
        <div class="filament-tables-component-wrapper">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
