<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Webhooks')" :subheading="__('Verwalte deine n8n Webhook-Endpunkte')">
        
        <!-- Add New Webhook -->
        <div class="space-y-6 w-full">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 dark:text-white">➕ Neuen Webhook hinzufügen</h2>
                
                <form wire:submit="addWebhookPreset" class="space-y-4">
                    <flux:input 
                        wire:model="newPresetName"
                        label="Name"
                        type="text"
                        placeholder="z.B. Standard AI, Fast Model, Creative..."
                        required
                    />

                    <flux:input 
                        wire:model="newPresetUrl"
                        label="Webhook URL"
                        type="url"
                        placeholder="https://n8n.getaxia.de/webhook/..."
                        required
                    />

                    <flux:textarea 
                        wire:model="newPresetDescription"
                        label="Beschreibung (optional)"
                        rows="2"
                        placeholder="z.B. Schnellerer Workflow mit GPT-4o-mini"
                    />

                    @if (session()->has('success'))
                        <flux:text class="!text-green-600 dark:!text-green-400 font-medium">
                            {{ session('success') }}
                        </flux:text>
                    @endif

                    @if (session()->has('error'))
                        <flux:text class="!text-red-600 dark:!text-red-400 font-medium">
                            {{ session('error') }}
                        </flux:text>
                    @endif

                    <flux:button 
                        type="submit"
                        variant="primary"
                        class="w-full"
                    >
                        💾 Webhook speichern
                    </flux:button>
                </form>
            </div>

            <!-- Saved Webhooks -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 dark:text-white">🔗 Gespeicherte Webhooks</h2>
                
                @if($presets->isEmpty())
                    <flux:text class="text-gray-500 dark:text-gray-400">
                        Noch keine Webhooks gespeichert.
                    </flux:text>
                @else
                    <div class="space-y-3">
                        @foreach($presets as $preset)
                            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 {{ $preset->is_active ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700' : '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg dark:text-white flex items-center gap-2">
                                            {{ $preset->name }}
                                            @if($preset->is_active)
                                                <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Aktiv</span>
                                            @endif
                                            @if($preset->is_default)
                                                <span class="text-xs bg-gray-500 text-white px-2 py-1 rounded">Standard</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 break-all">
                                            {{ $preset->webhook_url }}
                                        </p>
                                        @if($preset->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                {{ $preset->description }}
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <div class="flex gap-2 ml-4 flex-col sm:flex-row">
                                        @if(!$preset->is_active)
                                            <flux:button 
                                                wire:click="activatePreset('{{ $preset->id }}')"
                                                size="sm"
                                                variant="primary"
                                            >
                                                Aktivieren
                                            </flux:button>
                                        @endif
                                        
                                        <flux:button 
                                            wire:click="testWebhook('{{ $preset->id }}')"
                                            size="sm"
                                        >
                                            Test
                                        </flux:button>
                                        
                                        @if(!$preset->is_default)
                                            <flux:button 
                                                wire:click="deletePreset('{{ $preset->id }}')"
                                                wire:confirm="Webhook wirklich löschen?"
                                                size="sm"
                                                variant="danger"
                                            >
                                                Löschen
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Debug Info -->
            @if(config('app.debug'))
                <div class="bg-gray-100 dark:bg-zinc-900 rounded-lg p-4">
                    <h3 class="font-semibold mb-2 dark:text-white">🐛 Debug Info</h3>
                    <pre class="text-xs dark:text-gray-300">Presets Count: {{ $presets->count() }}</pre>
                    <pre class="text-xs dark:text-gray-300">User Webhook: {{ auth()->user()->n8n_webhook_url ?? 'NULL' }}</pre>
                    <pre class="text-xs dark:text-gray-300">Chart Webhook: {{ auth()->user()->chart_webhook_url ?? 'NULL' }}</pre>
                </div>
            @endif
            
            <!-- Chart Webhook Configuration -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <h2 class="text-xl font-semibold mb-2 dark:text-white flex items-center gap-2">
                    📊 Diagram Webhook
                    <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded">Spezialisiert</span>
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Dieser Webhook wird für Diagramm-Generierung (AntV Charts) verwendet
                </p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Chart Webhook URL
                        </label>
                        <input 
                            type="url"
                            value="{{ auth()->user()->chart_webhook_url ?? '' }}"
                            readonly
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-zinc-700 dark:text-white text-sm"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Aktuell: {{ auth()->user()->chart_webhook_url ? '✅ Konfiguriert' : '❌ Nicht gesetzt' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </x-settings.layout>
</section>
