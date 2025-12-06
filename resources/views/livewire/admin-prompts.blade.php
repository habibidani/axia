<div class="max-w-[1400px] mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">System Prompts</h1>
        <p>Manage AI prompts for task analysis and data extraction.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-green)]">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Tabs -->
    <div class="mb-6 flex gap-1 bg-[var(--bg-tertiary)] p-1 rounded-full w-fit">
        <button
            wire:click="$set('activeTab', 'todo_analysis')"
            class="px-4 py-1.5 text-sm rounded-full transition-all {{ $activeTab === 'todo_analysis' ? 'bg-[#E94B8C] text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]' }}"
        >
            Todo Analysis
        </button>
        <button
            wire:click="$set('activeTab', 'company_extraction')"
            class="px-4 py-1.5 text-sm rounded-full transition-all {{ $activeTab === 'company_extraction' ? 'bg-[#E94B8C] text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]' }}"
        >
            Company Extraction
        </button>
        <button
            wire:click="$set('activeTab', 'goals_extraction')"
            class="px-4 py-1.5 text-sm rounded-full transition-all {{ $activeTab === 'goals_extraction' ? 'bg-[#E94B8C] text-white' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]' }}"
        >
            Goals Extraction
        </button>
    </div>

    <!-- Add New Button -->
    <div class="mb-6">
        <button
            wire:click="createPrompt"
            class="px-6 py-2.5 bg-[#E94B8C] hover:bg-[#D43F7C] text-white text-sm rounded-lg transition-colors"
        >
            + Create New Prompt
        </button>
    </div>

    <!-- Prompts Table -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-[var(--bg-tertiary)]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide">Version</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide">System Message</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide">Temp</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @forelse($prompts as $prompt)
                        <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]">
                                {{ $prompt->version }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--text-secondary)]">
                                <div class="truncate max-w-md">{{ Str::limit($prompt->system_message, 80) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">
                                {{ $prompt->temperature }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($prompt->is_active)
                                    <span class="px-2 py-1 text-xs rounded-lg bg-[var(--accent-green-light)] text-[var(--accent-green)] border border-[rgba(76,175,80,0.3)]">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-lg bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)]">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                <button wire:click="editPrompt('{{ $prompt->id }}')" class="text-[var(--accent-pink)] hover:underline">Edit</button>
                                <button wire:click="clonePrompt('{{ $prompt->id }}')" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]">Clone</button>
                                <button wire:click="toggleActive('{{ $prompt->id }}')" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    {{ $prompt->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="deletePrompt('{{ $prompt->id }}')" class="text-[var(--accent-orange)] hover:underline">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-[var(--text-secondary)]">
                                No prompts yet for this type. Create one to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-[var(--bg-primary)]/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-medium text-[var(--text-primary)]">{{ $editingId ? 'Edit' : 'Create' }} Prompt</h2>
                        <button wire:click="$set('showModal', false)" class="text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-sm text-[var(--text-primary)]">Type</label>
                            <select wire:model="type" class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]">
                                <option value="todo_analysis">Todo Analysis</option>
                                <option value="company_extraction">Company Extraction</option>
                                <option value="goals_extraction">Goals Extraction</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm text-[var(--text-primary)]">Version</label>
                            <input type="text" wire:model="version" placeholder="v1.0" class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"/>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm text-[var(--text-primary)]">System Message</label>
                            <textarea wire:model="system_message" rows="8" class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] font-mono resize-none"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm text-[var(--text-primary)]">
                                User Prompt Template
                                <span class="text-xs text-[var(--text-secondary)] ml-2">Use @{{variable}} syntax</span>
                            </label>
                            <textarea wire:model="user_prompt_template" rows="12" class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] font-mono resize-none"></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm text-[var(--text-primary)]">
                                Temperature: {{ $temperature }}
                            </label>
                            <input type="range" wire:model="temperature" min="0" max="1" step="0.1" class="w-full accent-[#E94B8C]"/>
                            <div class="flex justify-between text-xs text-[var(--text-secondary)]">
                                <span>Precise (0.0)</span>
                                <span>Creative (1.0)</span>
                            </div>
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded border-[var(--border)] bg-[var(--bg-tertiary)] text-[#E94B8C] focus:ring-[#E94B8C] focus:ring-offset-0"/>
                            <span class="text-sm text-[var(--text-primary)]">Set as active prompt</span>
                        </label>

                        <div class="flex gap-3 pt-4 border-t border-[var(--border)]">
                            <button type="submit" class="flex-1 px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors">
                                {{ $editingId ? 'Update' : 'Create' }} Prompt
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-lg transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
