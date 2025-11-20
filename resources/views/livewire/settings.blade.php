<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6">Einstellungen</h1>

    <div class="space-y-6">
        <!-- Webhook URL Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">🔗 n8n Webhook URL</h2>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Konfiguriere die n8n Webhook URL für AI-Anfragen. Diese URL wird für alle AI-Analysen (Todo-Analyse, Chat, etc.) verwendet.
            </p>

            <div class="space-y-4">
                <div>
                    <label for="webhookUrl" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Webhook URL
                    </label>
                    <input 
                        type="url"
                        id="webhookUrl"
                        wire:model="webhookUrl"
                        placeholder="https://n8n.getaxia.de/webhook/..." 
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-zinc-700 dark:text-white"
                    />
                </div>

                @if (session()->has('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="flex gap-3">
                    <button 
                        wire:click="saveWebhookUrl" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        💾 Speichern
                    </button>

                    <button 
                        wire:click="testWebhook" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700"
                    >
                        🧪 Testen
                    </button>

                    <button 
                        wire:click="useDefaultWebhook" 
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg"
                    >
                        ↻ Standard wiederherstellen
                    </button>
                </div>
            </div>
        </div>

        <!-- Webhook Presets (Future) -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">📋 Webhook Vorlagen</h2>
            
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Wähle aus vorgefertigten Webhook-Konfigurationen oder erstelle eigene.
            </p>

            <div class="text-sm text-gray-500 italic">
                Feature kommt bald: Verschiedene AI-Modelle/Workflows auswählen
            </div>
        </div>

        <!-- Current Configuration Info -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">ℹ️ Aktuelle Konfiguration</h2>
            
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">Webhook URL:</dt>
                    <dd class="text-gray-600 dark:text-gray-400 break-all">{{ $webhookUrl ?: 'Nicht konfiguriert' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-700 dark:text-gray-300">User:</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ auth()->user()->email }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
