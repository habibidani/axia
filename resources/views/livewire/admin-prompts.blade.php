<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-4">
                ← Back to home
            </a>
            <h1 class="text-2xl font-bold text-gray-900">System Prompts</h1>
            <p class="mt-1 text-sm text-gray-600">Manage AI prompts for task analysis and data extraction.</p>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Tabs -->
        <div class="mb-6 flex gap-2 border-b border-gray-200">
            <button
                wire:click="$set('activeTab', 'todo_analysis')"
                class="px-4 py-2 text-sm font-medium {{ $activeTab === 'todo_analysis' ? 'text-rose-600 border-b-2 border-rose-600' : 'text-gray-600 hover:text-gray-900' }}"
            >
                Todo Analysis
            </button>
            <button
                wire:click="$set('activeTab', 'company_extraction')"
                class="px-4 py-2 text-sm font-medium {{ $activeTab === 'company_extraction' ? 'text-rose-600 border-b-2 border-rose-600' : 'text-gray-600 hover:text-gray-900' }}"
            >
                Company Extraction
            </button>
            <button
                wire:click="$set('activeTab', 'goals_extraction')"
                class="px-4 py-2 text-sm font-medium {{ $activeTab === 'goals_extraction' ? 'text-rose-600 border-b-2 border-rose-600' : 'text-gray-600 hover:text-gray-900' }}"
            >
                Goals Extraction
            </button>
        </div>

        <!-- Add New Button -->
        <div class="mb-6">
            <button
                wire:click="createPrompt"
                class="px-4 py-2 bg-rose-500 text-white text-sm font-medium rounded-lg hover:bg-rose-600 transition-colors"
            >
                + Create New Prompt
            </button>
        </div>

        <!-- Prompts Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">System Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Temp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($prompts as $prompt)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $prompt->version }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="truncate max-w-md">{{ $prompt->system_message }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $prompt->temperature }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($prompt->is_active)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="editPrompt('{{ $prompt->id }}')" class="text-rose-600 hover:text-rose-900 mr-3">Edit</button>
                                <button wire:click="clonePrompt('{{ $prompt->id }}')" class="text-blue-600 hover:text-blue-900 mr-3">Clone</button>
                                <button wire:click="toggleActive('{{ $prompt->id }}')" class="text-gray-600 hover:text-gray-900 mr-3">
                                    {{ $prompt->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="deletePrompt('{{ $prompt->id }}')" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                No prompts yet for this type. Create one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Edit Modal -->
        @if($showModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $editingId ? 'Edit' : 'Create' }} Prompt</h2>
                            <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="save" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select wire:model="type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                    <option value="todo_analysis">Todo Analysis</option>
                                    <option value="company_extraction">Company Extraction</option>
                                    <option value="goals_extraction">Goals Extraction</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Version</label>
                                <input type="text" wire:model="version" placeholder="v1.0" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">System Message</label>
                                <textarea wire:model="system_message" rows="8" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent font-mono text-sm resize-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    User Prompt Template
                                    <span class="text-xs text-gray-500 ml-2">Use {{variable}} syntax</span>
                                </label>
                                <textarea wire:model="user_prompt_template" rows="12" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent font-mono text-sm resize-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Temperature: {{ $temperature }}
                                </label>
                                <input type="range" wire:model="temperature" min="0" max="1" step="0.1" class="w-full"/>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Precise (0.0)</span>
                                    <span>Creative (1.0)</span>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 text-rose-500 border-gray-300 rounded focus:ring-rose-500"/>
                                <label for="is_active" class="ml-3 text-sm text-gray-700">Set as active prompt</label>
                            </div>

                            <div class="flex gap-3 pt-4 border-t border-gray-200">
                                <button type="submit" class="flex-1 bg-rose-500 text-white font-semibold py-3 px-6 rounded-xl hover:bg-rose-600 transition-colors">
                                    {{ $editingId ? 'Update' : 'Create' }} Prompt
                                </button>
                                <button type="button" wire:click="$set('showModal', false)" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

