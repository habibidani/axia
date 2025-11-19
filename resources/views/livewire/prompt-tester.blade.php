<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.prompts') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-4">
                ← Back to prompts
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Prompt Tester</h1>
            <p class="mt-1 text-sm text-gray-600">Test AI prompts with live or mock data.</p>
        </div>

        <!-- Settings -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Prompt Type</label>
                <select wire:model="promptType" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="todo_analysis">Todo Analysis</option>
                    <option value="company_extraction">Company Extraction</option>
                    <option value="goals_extraction">Goals Extraction</option>
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Source</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="radio" wire:model="useMockData" value="1" class="w-4 h-4 text-rose-500"/>
                        <span class="ml-2 text-sm text-gray-700">Mock Data</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model="useMockData" value="0" class="w-4 h-4 text-rose-500"/>
                        <span class="ml-2 text-sm text-gray-700">Live Context</span>
                    </label>
                </div>
            </div>
        </div>

        @if($activePrompt)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-sm text-blue-800">
                    Using: <strong>{{ $activePrompt->version }}</strong> (temp: {{ $activePrompt->temperature }})
                </p>
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                <p class="text-sm text-yellow-800">No active prompt for this type!</p>
            </div>
        @endif

        <!-- Input/Output Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Input -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Input</h2>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <textarea
                        wire:model="testInput"
                        rows="15"
                        placeholder="Enter test data..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none text-sm font-mono"
                    ></textarea>

                    <button
                        wire:click="test"
                        @disabled($testing || !$activePrompt)
                        class="mt-4 w-full bg-rose-500 text-white font-semibold py-3 px-6 rounded-xl hover:bg-rose-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        @if($testing)
                            <span class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Testing...
                            </span>
                        @else
                            Run Test
                        @endif
                    </button>
                </div>
            </div>

            <!-- Output -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Output</h2>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    @if($error)
                        <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-sm text-red-800 font-mono">{{ $error }}</p>
                        </div>
                    @elseif($result)
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Parsed Response</h3>
                                <pre class="bg-gray-50 rounded-lg p-4 text-xs font-mono overflow-x-auto">{{ json_encode($result['parsed'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            
                            @if(isset($result['tokens']))
                                <div class="text-sm text-gray-600">
                                    <strong>Tokens used:</strong> {{ number_format($result['tokens']) }}
                                </div>
                            @endif

                            <button
                                onclick="navigator.clipboard.writeText(document.querySelector('pre').textContent)"
                                class="text-sm text-rose-600 hover:text-rose-700 font-medium"
                            >
                                Copy to clipboard
                            </button>
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <p class="text-sm">No results yet. Run a test to see output.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

